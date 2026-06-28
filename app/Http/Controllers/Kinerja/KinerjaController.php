<?php

namespace App\Http\Controllers\Kinerja;

use App\Http\Controllers\Controller;
use App\Models\Kinerja\KpiIndikator;
use App\Models\Master\Lembaga;
use App\Models\Master\TahunPelajaran;
use App\Repositories\Kinerja\KpiRepositoryInterface;
use App\Services\Kinerja\KinerjaService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KinerjaController extends Controller
{
    public function __construct(
        protected KpiRepositoryInterface $repo,
        protected KinerjaService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    // ── Dashboard ────────────────────────────────────────────────────────────

    public function index()
    {
        $activeLembagaId = app('active_lembaga_id');

        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'kode', 'nama']);
        $tahunList   = TahunPelajaran::orderBy('nama', 'desc')->get(['id', 'nama', 'status']);
        $activeTahun = TahunPelajaran::where('status', 'aktif')->first();

        $this->logActivity->log(
            'Akses Dashboard Kinerja',
            'Membuka halaman dashboard kinerja sekolah.'
        );

        return view('admin.kinerja.index', compact(
            'lembagaList',
            'tahunList',
            'activeLembagaId',
            'activeTahun',
        ));
    }

    /**
     * AJAX endpoint — returns aggregated dashboard payload as JSON.
     */
    public function dashboard(Request $request)
    {
        $lembagaId = $request->input('lembaga_id', app('active_lembaga_id'));
        $tahunId   = $request->input('tahun_pelajaran_id');

        if (! $tahunId) {
            $tahun   = TahunPelajaran::where('status', 'aktif')->first();
            $tahunId = $tahun?->id;
        }

        if (! $lembagaId || ! $tahunId) {
            return $this->response->error('Pilih lembaga dan tahun pelajaran.', 422);
        }

        $data = $this->service->getDashboardData((int) $lembagaId, (int) $tahunId);

        return $this->response->success($data, 'OK');
    }

    // ── Show single KPI (AJAX) ───────────────────────────────────────────────

    public function show(int $id)
    {
        $ind = $this->repo->findById($id);

        if (! $ind) {
            return $this->response->error('Indikator tidak ditemukan.', 404);
        }

        return $this->response->success($ind, 'OK');
    }

    // ── Manage page ──────────────────────────────────────────────────────────

    public function manage()
    {
        $activeLembagaId = app('active_lembaga_id');

        $lembagaList    = Lembaga::orderBy('urutan')->get();
        $tahunList      = TahunPelajaran::orderBy('nama', 'desc')->get();
        $kategoriConfig = KpiIndikator::kategoriConfig();

        $this->logActivity->log(
            'Akses Kelola KPI',
            'Membuka halaman kelola indikator KPI.'
        );

        return view('admin.kinerja.manage', compact(
            'lembagaList',
            'tahunList',
            'activeLembagaId',
            'kategoriConfig',
        ));
    }

    /**
     * DataTables JSON for the manage page.
     */
    public function list(Request $request)
    {
        $lembagaId = $request->input('lembaga_id', app('active_lembaga_id'));
        $tahunId   = $request->input('tahun_pelajaran_id');

        $builder = KpiIndikator::query()
            ->where('lembaga_id', $lembagaId)
            ->when($tahunId, fn ($q) => $q->where('tahun_pelajaran_id', $tahunId))
            ->with(['latestRealisasi'])
            ->orderBy('kategori')
            ->orderBy('urutan');

        return DataTables::of($builder)
            ->addIndexColumn()
            ->addColumn('action', function (KpiIndikator $ind) {
                $edit   = "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editIndikator({$ind->id})' title='Edit'><i class='bi bi-pencil'></i></button>";
                $input  = "<button class='btn btn-xs btn-icon btn-light-info me-1' onclick='inputRealisasi({$ind->id})' title='Input Realisasi'><i class='bi bi-graph-up-arrow'></i></button>";
                $del    = "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusIndikator({$ind->id})' title='Hapus'><i class='bi bi-trash'></i></button>";
                return $edit . $input . $del;
            })
            ->addColumn('kategori_label', fn (KpiIndikator $ind) => $ind->kategori_label)
            ->addColumn('nilai_realisasi', fn (KpiIndikator $ind) => $ind->latestRealisasi?->nilai_realisasi ?? '—')
            ->addColumn('pencapaian_persen', function (KpiIndikator $ind) {
                $pct = $ind->pencapaian;
                if (! $ind->latestRealisasi) {
                    return '—';
                }
                return number_format($pct, 1) . '%';
            })
            ->addColumn('traffic_light_badge', fn (KpiIndikator $ind) => $ind->traffic_light_badge)
            ->addColumn('is_auto_badge', fn (KpiIndikator $ind) => $ind->is_auto_badge)
            ->rawColumns(['action', 'traffic_light_badge', 'is_auto_badge'])
            ->make(true);
    }

    // ── CRUD ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'lembaga_id'         => 'required|exists:lembaga,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama_indikator'     => 'required|string|max:255',
            'deskripsi'          => 'nullable|string|max:1000',
            'kategori'           => 'required|in:akademik,ppdb,program_kerja,kesiswaan,sarpras,humas,umum',
            'satuan'             => 'nullable|string|max:50',
            'target'             => 'required|numeric|min:0',
            'sumber_data'        => 'nullable|in:manual,akademik_nilai,ppdb_pendaftar,ppdb_diterima,program_kerja,absensi',
            'is_auto'            => 'boolean',
            'urutan'             => 'nullable|integer|min:0',
        ]);

        $data['is_auto'] = $request->boolean('is_auto');

        try {
            $ind = $this->service->createIndikator($data);

            return $this->response->success($ind, 'Indikator KPI berhasil dibuat.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'lembaga_id'         => 'required|exists:lembaga,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama_indikator'     => 'required|string|max:255',
            'deskripsi'          => 'nullable|string|max:1000',
            'kategori'           => 'required|in:akademik,ppdb,program_kerja,kesiswaan,sarpras,humas,umum',
            'satuan'             => 'nullable|string|max:50',
            'target'             => 'required|numeric|min:0',
            'sumber_data'        => 'nullable|in:manual,akademik_nilai,ppdb_pendaftar,ppdb_diterima,program_kerja,absensi',
            'is_auto'            => 'boolean',
            'urutan'             => 'nullable|integer|min:0',
        ]);

        $data['is_auto'] = $request->boolean('is_auto');

        try {
            $this->service->updateIndikator($id, $data);

            return $this->response->success(null, 'Indikator berhasil diperbarui.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->service->deleteIndikator($id);

            return $this->response->success(null, 'Indikator dihapus.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    // ── Realisasi ────────────────────────────────────────────────────────────

    public function inputRealisasi(Request $request, int $id)
    {
        $request->validate([
            'periode'          => 'required|string|max:100',
            'nilai_realisasi'  => 'required|numeric|min:0',
            'catatan'          => 'nullable|string|max:1000',
        ]);

        try {
            $r = $this->service->inputRealisasi(
                $id,
                $request->periode,
                (float) $request->nilai_realisasi,
                $request->catatan,
                auth()->user()->id_user
            );

            return $this->response->success($r, 'Realisasi berhasil disimpan.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    // ── Auto Sync ────────────────────────────────────────────────────────────

    public function syncAuto(Request $request)
    {
        $request->validate([
            'lembaga_id'         => 'required|exists:lembaga,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'periode'            => 'required|string|max:100',
        ]);

        try {
            $result = $this->service->syncAutoKpi(
                (int) $request->lembaga_id,
                (int) $request->tahun_pelajaran_id,
                $request->periode,
                auth()->user()->id_user
            );

            return $this->response->success(
                $result,
                "{$result['synced']} indikator otomatis diperbarui."
            );
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
