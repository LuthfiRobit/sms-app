<?php

namespace App\Http\Controllers\Portal;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Services\Portal\DaftarUlangService;

/**
 * DaftarUlangPesertaController
 *
 * Mengelola halaman dan endpoint daftar ulang PPDB dari sisi peserta (portal).
 *
 * KEAMANAN: Selalu gunakan auth()->user()->id_user (PK tabel users, bukan id).
 *
 * Routes (prefix: ppdb.daftar-ulang.*):
 *   GET   /daftar-ulang/{pendaftaranId}   → index()   [View]
 *   POST  /daftar-ulang/{pendaftaranId}   → store()   [JSON]
 */
class DaftarUlangPesertaController extends Controller
{
    public function __construct(
        protected DaftarUlangService $daftarUlangService,
    ) {
    }

    // =========================================================================
    // 1. INDEX — Halaman Daftar Ulang Peserta
    // =========================================================================

    /**
     * Menampilkan halaman daftar ulang untuk satu pendaftaran.
     *
     * Guard:
     *  - Ownership check dilakukan oleh DaftarUlangService
     *  - AuthorizationException → abort(403)
     *
     * Data yang dikirim ke view:
     *  - $info : array lengkap dari DaftarUlangService::getInfoDaftarUlang()
     *
     * @param  int  $pendaftaranId  ID pendaftaran dari URL segment
     * @return View
     */
    public function index(int $pendaftaranId): View
    {
        try {
            $userId = auth()->user()->id_user;

            $info = $this->daftarUlangService->getInfoDaftarUlang($pendaftaranId, $userId);

            return view('portal.daftar-ulang.index', compact('info'));
        } catch (AuthorizationException $e) {
            abort(403, 'Anda tidak memiliki akses ke halaman daftar ulang ini.');
        }
    }

    // =========================================================================
    // 2. STORE — Proses Konfirmasi Daftar Ulang (AJAX)
    // =========================================================================

    /**
     * Memproses konfirmasi daftar ulang oleh peserta.
     *
     * Validasi form:
     *  - pernyataan_hadir: required|accepted (checkbox harus dicentang)
     *
     * Response JSON:
     *  - { success: bool, message: string }
     *
     * @param  Request  $request
     * @param  int      $pendaftaranId  ID pendaftaran dari URL segment
     * @return JsonResponse
     */
    public function store(Request $request, int $pendaftaranId): JsonResponse
    {
        try {
            // Validasi: peserta harus mencentang pernyataan kehadiran
            $request->validate([
                'pernyataan_hadir' => 'required|accepted',
            ], [
                'pernyataan_hadir.required' => 'Anda harus menyatakan kesediaan hadir terlebih dahulu.',
                'pernyataan_hadir.accepted'  => 'Anda harus mencentang pernyataan kehadiran untuk melanjutkan.',
            ]);

            $userId = auth()->user()->id_user;

            $result = $this->daftarUlangService->konfirmasiDaftarUlang($pendaftaranId, $userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi admin.',
            ], 500);
        }
    }
}
