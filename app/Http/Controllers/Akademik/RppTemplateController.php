<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Services\Akademik\RppTemplateService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Mengelola STRUKTUR templat RPP (bagian, poin, opsi master) — halaman
 * "Kelola Bagian & Poin RPP". Ini titik satu-satunya admin perlu sentuh
 * kalau templat RPP direvisi Kemenag/Ma'arif, tanpa migrasi/deploy kode.
 */
class RppTemplateController extends Controller
{
    public function __construct(
        protected RppTemplateService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Kelola Bagian & Poin RPP', 'Membuka halaman kelola templat RPP.');

        $bagianList = $this->service->semuaBagian();

        return view('admin.akademik.rpp-template.index', compact('bagianList'));
    }

    // ── Bagian ───────────────────────────────────────────────────────────────

    public function storeBagian(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:150',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $bagian = $this->service->storeBagian($data);

        return $this->response->success($bagian, 'Bagian berhasil ditambahkan.');
    }

    public function updateBagian(Request $request, int $id)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:150',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $bagian = $this->service->updateBagian($id, $data);

        return $this->response->success($bagian, 'Bagian berhasil diperbarui.');
    }

    public function destroyBagian(int $id)
    {
        try {
            $this->service->destroyBagian($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success(null, 'Bagian berhasil dihapus.');
    }

    // ── Poin ─────────────────────────────────────────────────────────────────

    public function storePoin(Request $request)
    {
        $data = $this->validatePoin($request);
        $poin = $this->service->storePoin($data);

        return $this->response->success($poin, 'Poin berhasil ditambahkan.');
    }

    public function updatePoin(Request $request, int $id)
    {
        $data = $this->validatePoin($request);
        $poin = $this->service->updatePoin($id, $data);

        return $this->response->success($poin, 'Poin berhasil diperbarui.');
    }

    public function destroyPoin(int $id)
    {
        try {
            $this->service->destroyPoin($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success(null, 'Poin berhasil dihapus.');
    }

    public function reorderPoin(Request $request)
    {
        $data = $request->validate([
            'urutan' => 'required|array',
            'urutan.*' => 'integer',
        ]);

        $this->service->reorderPoin($data['urutan']);

        return $this->response->success(null, 'Urutan poin berhasil disimpan.');
    }

    private function validatePoin(Request $request): array
    {
        return $request->validate([
            'rpp_bagian_id' => 'required|exists:rpp_bagian,id',
            'kode' => 'required|string|max:100|alpha_dash',
            'label' => 'required|string|max:255',
            'tipe' => 'required|in:teks,teks_panjang,daftar_poin,pasangan_kolom,pilih_master,model_pembelajaran',
            'kolom1_label' => 'nullable|required_if:tipe,pasangan_kolom|string|max:100',
            'kolom2_label' => 'nullable|required_if:tipe,pasangan_kolom|string|max:100',
            'master_kategori' => 'nullable|required_if:tipe,pilih_master|string|max:100',
            'is_required' => 'nullable|boolean',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);
    }

    // ── Opsi Master (pilih_master) ──────────────────────────────────────────

    public function opsiIndex(string $kategori)
    {
        return $this->response->success($this->service->opsiByKategori($kategori), 'OK');
    }

    public function storeOpsi(Request $request)
    {
        $data = $request->validate([
            'kategori' => 'required|string|max:100',
            'nama' => 'required|string|max:255',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $opsi = $this->service->storeOpsi($data);

        return $this->response->success($opsi, 'Opsi berhasil ditambahkan.');
    }

    public function updateOpsi(Request $request, int $id)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $opsi = $this->service->updateOpsi($id, $data);

        return $this->response->success($opsi, 'Opsi berhasil diperbarui.');
    }

    public function destroyOpsi(int $id)
    {
        try {
            $this->service->destroyOpsi($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success(null, 'Opsi berhasil dihapus.');
    }
}
