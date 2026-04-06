<?php

namespace App\Http\Controllers\Portal;

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
 *   GET  /pendaftaran            → index()
 *   GET  /pendaftaran/pilih      → pilihJalur()
 *   POST /pendaftaran            → store()
 *   GET  /pendaftaran/{id}       → show()          [placeholder]
 *   PUT  /pendaftaran/{id}/formulir → saveFormulir() [placeholder]
 *   POST /pendaftaran/{id}/dokumen/{syaratId} → uploadDokumen() [placeholder]
 *   DELETE /pendaftaran/{id}/dokumen/{dokumenId} → hapusDokumen() [placeholder]
 *   POST /pendaftaran/{id}/submit → submit()       [placeholder]
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
    // PLACEHOLDER — Methods yang akan diimplementasi berikutnya
    // =========================================================================

    public function show($id): View
    {
        return view('portal.coming_soon', ['fitur' => 'Detail Pendaftaran']);
    }

    public function saveFormulir(Request $request, $id): RedirectResponse
    {
        return back()->with('info', 'Fitur dalam pengembangan.');
    }

    public function uploadDokumen(Request $request, $id, $syaratId): RedirectResponse
    {
        return back()->with('info', 'Fitur dalam pengembangan.');
    }

    public function hapusDokumen($id, $dokumenId): RedirectResponse
    {
        return back()->with('info', 'Fitur dalam pengembangan.');
    }

    public function submit($id): RedirectResponse
    {
        return back()->with('info', 'Fitur dalam pengembangan.');
    }
}
