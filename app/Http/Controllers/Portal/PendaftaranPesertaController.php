<?php

namespace App\Http\Controllers\Portal;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Services\Portal\PortalPendaftaranService;
use App\Services\Peserta\PesertaProfileService;

/**
 * PendaftaranPesertaController
 *
 * Mengelola alur pendaftaran PPDB dari sisi peserta (portal).
 *
 * KEAMANAN: Selalu gunakan auth()->user()->id_user (PK tabel users),
 * BUKAN auth()->id() karena PK bisa berbeda.
 *
 * Routes (prefix: ppdb.pendaftaran.*):
 *   GET    /pendaftaran                              → index()
 *   GET    /pendaftaran/pilih                        → pilihJalur()
 *   POST   /pendaftaran                              → store()
 *   GET    /pendaftaran/{id}                         → show()
 *   PUT    /pendaftaran/{id}/formulir                → saveFormulir()   [JSON]
 *   POST   /pendaftaran/{id}/dokumen/{syaratId}      → uploadDokumen()  [JSON]
 *   DELETE /pendaftaran/{id}/dokumen/{dokumenId}     → hapusDokumen()   [JSON]
 *   POST   /pendaftaran/{id}/submit                  → submit()         [JSON]
 */
class PendaftaranPesertaController extends Controller
{
    public function __construct(
        protected PortalPendaftaranService $portalPendaftaranSvc,
        protected PesertaProfileService    $pesertaProfileSvc,
    ) {
    }

    // =========================================================================
    // 1. INDEX — Daftar semua pendaftaran milik peserta
    // =========================================================================

    /**
     * Menampilkan halaman daftar pendaftaran peserta yang sedang login.
     *
     * Data diambil via PortalPendaftaranService->getPendaftaranSaya() yang
     * sudah di-scope ke peserta_id dari userId yang terautentikasi — aman
     * tanpa perlu ownership check tambahan.
     */
    public function index(): View
    {
        $data = $this->portalPendaftaranSvc->getPendaftaranSaya(
            auth()->user()->id_user
        );

        return view('portal.pendaftaran.index', compact('data'));
    }

    // =========================================================================
    // 2. PILIH JALUR — Form pemilihan jalur pendaftaran
    // =========================================================================

    /**
     * Menampilkan halaman pilih jalur pendaftaran.
     *
     * Dua data yang dikirim ke view:
     *  - $jalur     : semua jalur tersedia berikut flag bisa_daftar, sudah_daftar, kuota
     *  - $cekProfil : apakah profil peserta sudah cukup untuk mendaftar
     *
     * View bertanggung jawab menampilkan warning jika profil belum lengkap.
     */
    public function pilihJalur(): View
    {
        $userId = auth()->user()->id_user;

        $jalur     = $this->portalPendaftaranSvc->getJalurTersedia($userId);
        $cekProfil = $this->pesertaProfileSvc->isProfilCukupUntukDaftar($userId);

        return view('portal.pendaftaran.pilih-jalur', compact('jalur', 'cekProfil'));
    }

    // =========================================================================
    // 3. STORE — Mulai pendaftaran baru
    // =========================================================================

    /**
     * Memproses permintaan memulai pendaftaran pada jalur yang dipilih.
     *
     * Flow:
     *  1. Validasi input: jalur_pendaftaran_id wajib ada dan exist di DB
     *  2. Delegasi ke PortalPendaftaranService->mulaiPendaftaran()
     *     (service sudah menangani validasi bisa_daftar + kelengkapan profil)
     *  3. Jika sukses → redirect ke halaman detail pendaftaran baru
     *  4. Jika gagal  → redirect back dengan error message
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'jalur_pendaftaran_id' => 'required|integer|exists:jalur_pendaftaran,id',
        ], [
            'jalur_pendaftaran_id.required' => 'Silakan pilih jalur pendaftaran.',
            'jalur_pendaftaran_id.integer'  => 'Jalur pendaftaran tidak valid.',
            'jalur_pendaftaran_id.exists'   => 'Jalur pendaftaran tidak ditemukan.',
        ]);

        $userId  = auth()->user()->id_user;
        $jalurId = (int) $request->input('jalur_pendaftaran_id');

        $result = $this->portalPendaftaranSvc->mulaiPendaftaran($userId, $jalurId);

        if (!$result['success']) {
            return redirect()
                ->route('ppdb.pendaftaran.pilih')
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('ppdb.pendaftaran.show', $result['data']->id)
            ->with('success', $result['message']);
    }

    // =========================================================================
    // 4. SHOW — Halaman detail & pengisian pendaftaran
    // =========================================================================

    /**
     * Menampilkan halaman detail pendaftaran beserta formulir dan dokumen.
     *
     * Data yang dikirim ke view:
     *  - $detail['data']['pendaftaran'] : model Pendaftaran
     *  - $detail['data']['peserta']     : model Peserta
     *  - $detail['data']['jalur']       : model JalurPendaftaran
     *  - $detail['data']['field_values']: Collection PendaftaranFieldValue
     *  - $detail['data']['dokumen']     : array formatted dokumen
     *  - $detail['data']['verifier']    : array|null verifier
     *  - $progress['data']              : {formulir, dokumen, siap_submit}
     *
     * AuthorizationException di-catch dan di-abort(403) agar HTTP response konsisten.
     */
    public function show(int $id): View
    {
        try {
            $userId = auth()->user()->id_user;

            $detail   = $this->portalPendaftaranSvc->getDetailPendaftaran($id, $userId);
            $progress = $this->portalPendaftaranSvc->getProgressDetail($id, $userId);

            // Ambil daftar syarat dari jalur untuk section dokumen
            // (syarat dibutuhkan untuk render card per syarat, termasuk yang belum diupload)
            $jalur  = $detail['data']['jalur'] ?? null;
            $syarat = $jalur
                ? ($jalur->syaratPendaftaran ?? collect())
                : collect();

            // Load relasi syaratPendaftaran jika belum ter-load
            if ($jalur && !$jalur->relationLoaded('syaratPendaftaran')) {
                $jalur->load('syaratPendaftaran');
                $syarat = $jalur->syaratPendaftaran;
            }

            // Ambil formulir fields dari jalur (for rendering)
            $formulirFields = collect();
            if ($jalur) {
                $jalur->loadMissing(['formulirPendaftaran.formulirField']);
                $formulirFields = $jalur->formulirPendaftaran->first()
                    ?->formulirField ?? collect();
            }

            return view('portal.pendaftaran.show', compact(
                'detail',
                'progress',
                'syarat',
                'formulirFields',
            ));
        } catch (AuthorizationException $e) {
            abort(403, 'Anda tidak memiliki akses ke pendaftaran ini.');
        }
    }

    // =========================================================================
    // 5. SAVE FORMULIR — Auto-save field values via AJAX (JSON)
    // =========================================================================

    /**
     * Menyimpan field values formulir dari auto-save AJAX.
     *
     * Request body (JSON atau form-data):
     * { "fields": [{"formulir_field_id": int, "value": mixed}, ...] }
     *
     * @return JsonResponse {success, message, progress: {formulir: {persen, terisi, total}}}
     */
    public function saveFormulir(Request $request, int $id): JsonResponse
    {
        try {
            $userId      = auth()->user()->id_user;
            $fieldValues = $request->input('fields', []);

            $result = $this->portalPendaftaranSvc->saveFieldValues($id, $userId, $fieldValues);

            // Ambil progress terbaru setelah save
            $progressResult = $this->portalPendaftaranSvc->getProgressDetail($id, $userId);
            $progressData   = $progressResult['data'] ?? null;

            return response()->json([
                'success'  => $result['success'],
                'message'  => $result['message'],
                'progress' => $progressData ? [
                    'formulir' => [
                        'persen' => $progressData['formulir']['persen'],
                        'terisi' => $progressData['formulir']['terisi'],
                        'total'  => $progressData['formulir']['total'],
                    ],
                    'siap_submit' => $progressData['siap_submit'],
                ] : null,
            ], $result['success'] ? 200 : 422);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
    }

    // =========================================================================
    // 6. UPLOAD DOKUMEN — Upload file lewat AJAX (JSON)
    // =========================================================================

    /**
     * Mengunggah dokumen persyaratan via AJAX multipart/form-data.
     *
     * @return JsonResponse {success, message, dokumen: {id, nama_file, status_verifikasi, url, ukuran}}
     */
    public function uploadDokumen(Request $request, int $id, int $syaratId): JsonResponse
    {
        $request->validate([
            'dokumen' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ], [
            'dokumen.required' => 'File dokumen wajib dipilih.',
            'dokumen.file'     => 'Upload harus berupa file.',
            'dokumen.max'      => 'Ukuran file maksimal 5MB.',
            'dokumen.mimes'    => 'Format file harus PDF, JPG, atau PNG.',
        ]);

        try {
            $userId = auth()->user()->id_user;
            $file   = $request->file('dokumen');

            $result = $this->portalPendaftaranSvc->uploadDokumen($id, $userId, $syaratId, $file);

            $dokumenFormatted = null;
            if ($result['success'] && $result['data']) {
                $d = $result['data'];
                $dokumenFormatted = [
                    'id'                => $d->id,
                    'nama_file'         => $d->nama_file,
                    'ukuran_file'       => $d->ukuran_file,
                    'status_verifikasi' => $d->status_verifikasi,
                    'url'               => $d->url_file ?? null,
                    'mime_type'         => $d->mime_type,
                ];
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'dokumen' => $dokumenFormatted,
            ], $result['success'] ? 200 : 422);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
    }

    // =========================================================================
    // 7. HAPUS DOKUMEN — Hapus file via AJAX (JSON)
    // =========================================================================

    /**
     * Menghapus dokumen yang sudah diupload via AJAX.
     *
     * @return JsonResponse {success, message}
     */
    public function hapusDokumen(Request $request, int $id, int $dokumenId): JsonResponse
    {
        try {
            $userId = auth()->user()->id_user;
            $result = $this->portalPendaftaranSvc->hapusDokumen($id, $userId, $dokumenId);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ], $result['success'] ? 200 : 422);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
    }

    // =========================================================================
    // 8. SUBMIT — Ajukan pendaftaran via AJAX (JSON)
    // =========================================================================

    /**
     * Mengajukan pendaftaran dari status draft ke submit via AJAX.
     *
     * @return JsonResponse {success, message, errors_detail: array|null}
     */
    public function submit(Request $request, int $id): JsonResponse
    {
        try {
            $userId = auth()->user()->id_user;
            $result = $this->portalPendaftaranSvc->submitPendaftaran($id, $userId);

            return response()->json([
                'success'       => $result['success'],
                'message'       => $result['message'],
                'errors_detail' => $result['errors_detail'] ?? null,
            ], $result['success'] ? 200 : 422);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
    }
}
