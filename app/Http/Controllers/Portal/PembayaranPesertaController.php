<?php

namespace App\Http\Controllers\Portal;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Services\Portal\PortalPendaftaranService;
use App\Services\Transaksi\PembayaranService;
use App\Services\LogActivityService;
use App\Models\Transaksi\PembayaranPpdb;
use App\Repositories\Transaksi\PembayaranPpdbRepositoryInterface;

/**
 * PembayaranPesertaController
 *
 * Mengelola halaman dan endpoint pembayaran PPDB dari sisi peserta (portal).
 *
 * KEAMANAN: Selalu gunakan auth()->user()->id_user (PK tabel users).
 *
 * Routes (prefix: ppdb.pembayaran.*):
 *   GET    /pembayaran/{id}                    → index()              [View]
 *   POST   /pembayaran/{id}/token              → getToken()           [JSON]
 *   POST   /pembayaran/{id}/konfirmasi-manual  → konfirmasiManual()   [JSON]
 */
class PembayaranPesertaController extends Controller
{
    public function __construct(
        protected PortalPendaftaranService          $portalPendaftaranSvc,
        protected PembayaranPpdbRepositoryInterface $pembayaranRepo,
        protected PembayaranService                 $pembayaranService,
        protected LogActivityService                $logActivity,
    ) {
    }

    // =========================================================================
    // 1. INDEX — Halaman Pembayaran Peserta
    // =========================================================================

    /**
     * Menampilkan halaman pembayaran untuk satu pendaftaran.
     *
     * Guard:
     *  - Ownership check via PortalPendaftaranService->getDetailPendaftaran()
     *  - AuthorizationException → abort(403)
     *  - Status pendaftaran harus >= 'submit' (draft belum bisa bayar)
     *
     * Data yang dikirim ke view:
     *  - $pendaftaran : model Pendaftaran
     *  - $peserta     : model Peserta
     *  - $jalur       : model JalurPendaftaran
     *  - $biaya       : BiayaRegistrasi aktif untuk jalur ini (atau null)
     *  - $pembayaran  : PembayaranPpdb terkini milik pendaftaran ini (atau null)
     */
    public function index(int $id): View
    {
        try {
            $userId = auth()->user()->id_user;

            // Ownership check + ambil detail
            $detail = $this->portalPendaftaranSvc->getDetailPendaftaran($id, $userId);

            $pendaftaran = $detail['data']['pendaftaran'] ?? null;
            $peserta     = $detail['data']['peserta']     ?? null;
            $jalur       = $detail['data']['jalur']       ?? null;

            // Validasi status: harus sudah submit (bukan draft)
            if ($pendaftaran && $pendaftaran->status === \App\Models\Transaksi\Pendaftaran::STATUS_DRAFT) {
                return view('portal.pembayaran.index', [
                    'pendaftaran' => $pendaftaran,
                    'peserta'     => $peserta,
                    'jalur'       => $jalur,
                    'biaya'       => null,
                    'pembayaran'  => null,
                    'error_status' => 'Pendaftaran Anda masih berstatus <strong>Draft</strong>. '
                        . 'Silakan lengkapi dan submit pendaftaran terlebih dahulu sebelum dapat melakukan pembayaran.',
                ]);
            }

            // Ambil biaya registrasi aktif untuk jalur ini
            $biaya = null;
            if ($jalur) {
                $jalur->loadMissing(['biayaRegistrasi']);
                $biaya = $jalur->biayaRegistrasi->where('is_aktif', true)->first();
            }

            // Ambil record pembayaran terbaru milik pendaftaran ini (jika ada)
            $pembayaran = $this->pembayaranRepo
                ->datatable(['pendaftaran_id' => $id])
                ->with(['biayaRegistrasi'])
                ->latest()
                ->first();

            return view('portal.pembayaran.index', compact(
                'pendaftaran',
                'peserta',
                'jalur',
                'biaya',
                'pembayaran',
            ));
        } catch (AuthorizationException $e) {
            abort(403, 'Anda tidak memiliki akses ke halaman pembayaran ini.');
        }
    }

    // =========================================================================
    // 2. GET TOKEN — Midtrans Snap Token
    // =========================================================================

    /**
     * Mengambil Snap Token Midtrans untuk pembayaran online.
     *
     * Mendelegasikan ke PembayaranService::createSnapToken().
     * Response: {success, message, data: {snap_token, client_key, amount, order_id}}
     *
     * @return JsonResponse
     */
    public function getToken(Request $request, int $id): JsonResponse
    {
        try {
            $result = $this->pembayaranService->createSnapToken($id, auth()->user()->id_user);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // =========================================================================
    // 3. KONFIRMASI MANUAL — Upload Bukti Transfer (AKTIF)
    // =========================================================================

    /**
     * Memproses upload bukti pembayaran transfer manual.
     *
     * Flow:
     *  1. Ownership check
     *  2. Validasi file: required|file|max:5120|mimes:jpg,jpeg,png,pdf
     *  3. Upload ke storage/public/pembayaran/{no_pendaftaran}/bukti_{timestamp}.{ext}
     *  4. Upsert PembayaranPpdb: status='pending', metode='manual', bukti_bayar=path
     *  5. Log activity
     *  6. Return JSON {success, message}
     *
     * @return JsonResponse {success: bool, message: string}
     */
    public function konfirmasiManual(Request $request, int $id): JsonResponse
    {
        try {
            $userId = auth()->user()->id_user;

            // Ownership check
            $detail = $this->portalPendaftaranSvc->getDetailPendaftaran($id, $userId);
            $pendaftaran = $detail['data']['pendaftaran'] ?? null;

            if (!$pendaftaran) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pendaftaran tidak ditemukan.',
                ], 404);
            }

            // Validasi file bukti bayar
            $request->validate([
                'bukti_bayar' => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf',
                'keterangan'  => 'nullable|string|max:500',
            ], [
                'bukti_bayar.required' => 'File bukti pembayaran wajib diupload.',
                'bukti_bayar.file'     => 'Upload harus berupa file.',
                'bukti_bayar.max'      => 'Ukuran file maksimal 5MB.',
                'bukti_bayar.mimes'    => 'Format file harus JPG, PNG, atau PDF.',
                'keterangan.max'       => 'Keterangan maksimal 500 karakter.',
            ]);

            // Buat path upload: pembayaran/{no_pendaftaran}/bukti_{timestamp}.{ext}
            $file      = $request->file('bukti_bayar');
            $noPendaftaran = $pendaftaran->no_pendaftaran ?? ('PPDB-' . $pendaftaran->id);
            $timestamp = now()->format('YmdHis');
            $ext       = $file->getClientOriginalExtension();
            $path      = $file->storeAs(
                'pembayaran/' . $noPendaftaran,
                "bukti_{$timestamp}.{$ext}",
                'public'
            );

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupload file. Silakan coba lagi.',
                ], 500);
            }

            // Ambil biaya registrasi aktif untuk jalur pendaftaran ini
            $jalur = $detail['data']['jalur'] ?? null;
            $biayaId = null;
            $nominal = null;
            if ($jalur) {
                $jalur->loadMissing(['biayaRegistrasi']);
                $biayaAktif = $jalur->biayaRegistrasi->where('is_aktif', true)->first();
                $biayaId    = $biayaAktif?->id;
                $nominal    = $biayaAktif?->nominal;
            }

            // Cek apakah sudah ada record pembayaran untuk pendaftaran ini
            $existing = $this->pembayaranRepo
                ->datatable(['pendaftaran_id' => $id])
                ->latest()
                ->first();

            if ($existing) {
                // Update record yang sudah ada
                // Hapus file lama jika ada
                if ($existing->bukti_bayar && Storage::disk('public')->exists($existing->bukti_bayar)) {
                    Storage::disk('public')->delete($existing->bukti_bayar);
                }

                $this->pembayaranRepo->update($existing->id, [
                    'status'     => PembayaranPpdb::STATUS_PENDING,
                    'bukti_bayar' => $path,
                    'metode'     => 'manual',
                    'keterangan' => $request->input('keterangan'),
                    'waktu_bayar' => now(),
                    'amount'     => $nominal ?? $existing->amount,
                ]);
            } else {
                // Buat record baru
                $orderId = 'MANUAL-' . $noPendaftaran . '-' . $timestamp;
                $this->pembayaranRepo->create([
                    'pendaftaran_id'    => $id,
                    'biaya_registrasi_id' => $biayaId,
                    'metode'            => 'manual',
                    'status'            => PembayaranPpdb::STATUS_PENDING,
                    'order_id'          => $orderId,
                    'amount'            => $nominal ?? 0,
                    'bukti_bayar'       => $path,
                    'waktu_bayar'       => now(),
                    'keterangan'        => $request->input('keterangan'),
                ]);
            }

            // Log activity
            $this->logActivity->log(
                'Konfirmasi Pembayaran Manual',
                "Peserta mengunggah bukti pembayaran untuk pendaftaran #{$noPendaftaran} (ID: {$id})."
            );



            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diupload. '
                    . 'Admin akan memverifikasi dalam 1×24 jam kerja.',
            ]);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[PembayaranPesertaController::konfirmasiManual] ' . $e->getMessage(), [
                'user_id'        => auth()->user()->id_user ?? null,
                'pendaftaran_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi admin.',
            ], 500);
        }
    }
    /**
     * Memaksa sinkronisasi status pembayaran dengan Midtrans API.
     * Digunakan oleh frontend setelah callback Snap (success/pending).
     *
     * @return JsonResponse
     */
    public function syncStatus(Request $request, int $id): JsonResponse
    {
        try {
            $userId = auth()->user()->id_user;

            // Ownership check
            $this->portalPendaftaranSvc->getDetailPendaftaran($id, $userId);

            // Ambil record pembayaran terbaru untuk pendaftaran ini
            $pembayaran = $this->pembayaranRepo
                ->datatable(['pendaftaran_id' => $id])
                ->latest()
                ->first();

            if (!$pembayaran || !$pembayaran->order_id || $pembayaran->metode !== 'midtrans') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada transaksi Midtrans aktif untuk pendaftaran ini.',
                ], 404);
            }

            // Panggil service untuk sinkronisasi
            $result = $this->pembayaranService->syncStatus($pembayaran->order_id);

            return response()->json($result);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        } catch (\Exception $e) {
            Log::error('[PembayaranPesertaController::syncStatus] ' . $e->getMessage(), [
                'pendaftaran_id' => $id,
                'user_id'        => auth()->user()->id_user ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Membatalkan transaksi Midtrans yang berstatus pending (Ganti Metode).
     *
     * @return JsonResponse
     */
    public function cancelPending(Request $request, int $id): JsonResponse
    {
        try {
            $userId = auth()->user()->id_user;

            // Ownership check
            $this->portalPendaftaranSvc->getDetailPendaftaran($id, $userId);

            // Ambil record pembayaran terbaru
            $pembayaran = $this->pembayaranRepo
                ->datatable(['pendaftaran_id' => $id])
                ->latest()
                ->first();

            if (!$pembayaran || $pembayaran->status !== PembayaranPpdb::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada transaksi pending yang bisa dibatalkan.',
                ], 404);
            }

            // Panggil service untuk membatalkan
            $result = $this->pembayaranService->cancelPayment($pembayaran->id, $userId);

            return response()->json($result);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        } catch (\Exception $e) {
            Log::error('[PembayaranPesertaController::cancelPending] ' . $e->getMessage(), [
                'pendaftaran_id' => $id,
                'user_id'        => auth()->user()->id_user ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
