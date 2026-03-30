<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Master\TahunPelajaran;
use App\Models\Ppdb\JalurPendaftaran;
use App\Services\LogActivityService;
use App\Services\Ppdb\KuotaJurusanService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class KuotaJurusanController extends Controller
{
    protected $kuotaService;
    protected $logActivity;
    protected $responseService;

    public function __construct(
        KuotaJurusanService $kuotaService,
        LogActivityService $logActivity,
        ResponseService $responseService
    ) {
        $this->kuotaService = $kuotaService;
        $this->logActivity = $logActivity;
        $this->responseService = $responseService;
    }

    /**
     * Tampilkan halaman index dengan tabel kuota per jalur + tahun pelajaran.
     * Jalur dan tahun pelajaran dipilih via filter GET parameter.
     */
    public function index(Request $request)
    {
        $this->logActivity->log('View Kuota Jurusan', 'Membuka halaman Kuota Jurusan PPDB');

        $jalurList = JalurPendaftaran::with('pembukaanPpdb')->orderBy('nama')->get();
        $tahunPelajaranList = TahunPelajaran::orderByDesc('mulai')->get();

        // Jika ada filter dari GET, langsung load data kuota
        $jalurId = $request->get('jalur_id');
        $tahunPelajaranId = $request->get('tahun_pelajaran_id');
        $kuotaData = [];
        $jalurSelected = null;
        $tahunSelected = null;

        if ($jalurId && $tahunPelajaranId) {
            $result = $this->kuotaService->index((int) $jalurId, (int) $tahunPelajaranId);
            $kuotaData = $result['success'] ? $result['data'] : collect();
            $jalurSelected = JalurPendaftaran::with('pembukaanPpdb')->find($jalurId);
            $tahunSelected = TahunPelajaran::find($tahunPelajaranId);
        }

        return view('admin.ppdb.kuota-jurusan.index', compact(
            'jalurList',
            'tahunPelajaranList',
            'kuotaData',
            'jalurSelected',
            'tahunSelected',
            'jalurId',
            'tahunPelajaranId'
        ));
    }

    /**
     * Upsert semua kuota sekaligus untuk satu jalur + tahun pelajaran.
     * Body JSON: { jalur_id, tahun_pelajaran_id, kuota_data: [{jurusan_id, kuota}] }
     */
    public function upsert(Request $request)
    {
        $validatedData = $request->validate([
            'jalur_id' => 'required|exists:jalur_pendaftaran,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kuota_data' => 'required|array|min:1',
            'kuota_data.*.jurusan_id' => 'required|exists:jurusan,id',
            'kuota_data.*.kuota' => 'required|integer|min:0',
        ]);

        $userId = auth()->id() ?? 0;

        $result = $this->kuotaService->upsert(
            (int) $validatedData['jalur_id'],
            (int) $validatedData['tahun_pelajaran_id'],
            $validatedData['kuota_data'],
            $userId
        );

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    /**
     * Kembalikan status kuota (API untuk AJAX refresh).
     */
    public function status(Request $request)
    {
        $request->validate([
            'jalur_id' => 'required|exists:jalur_pendaftaran,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
        ]);

        $result = $this->kuotaService->getKuotaStatus(
            (int) $request->jalur_id,
            (int) $request->tahun_pelajaran_id
        );

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }
}
