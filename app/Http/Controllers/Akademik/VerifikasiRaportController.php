<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Models\Master\Rombel;
use App\Models\Master\Semester;
use App\Repositories\Akademik\PengajuanRaportRepositoryInterface;
use App\Services\Akademik\PengajuanRaportService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VerifikasiRaportController extends Controller
{
    public function __construct(
        protected PengajuanRaportRepositoryInterface $repo,
        protected PengajuanRaportService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log(
            'Akses Verifikasi Raport',
            'Membuka halaman verifikasi pengajuan raport.'
        );

        $activeLembagaId = app('active_lembaga_id');

        $lembagaList  = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);
        $rombelList   = Rombel::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'nama', 'tingkat', 'lembaga_id']);
        $semesterList = Semester::orderBy('nama')->get(['id', 'nama']);

        return view('admin.akademik.raport.verifikasi.index', compact(
            'lembagaList', 'rombelList', 'semesterList'
        ));
    }

    public function list(Request $request)
    {
        $activeLembagaId = app('active_lembaga_id');

        $filters = ['status' => 'diajukan'];

        if ($activeLembagaId) {
            $filters['lembaga_id'] = $activeLembagaId;
        }

        if ($request->filled('semester_id')) {
            $filters['semester_id'] = $request->integer('semester_id');
        }

        if ($request->filled('rombel_id')) {
            $filters['rombel_id'] = $request->integer('rombel_id');
        }

        $query = $this->service->datatable($filters);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('rombel_nama', fn($r) => $r->rombel
                ? "Kelas {$r->rombel->tingkat} - {$r->rombel->nama}"
                : '—')
            ->addColumn('wali_kelas', fn($r) => $r->rombel?->wali_kelas ?? '—')
            ->addColumn('semester_nama', fn($r) => $r->semester?->nama ?? '—')
            ->addColumn('tahun_nama', fn($r) => $r->tahunPelajaran?->nama ?? '—')
            ->addColumn('diajukan_at_fmt', fn($r) => $r->diajukan_at
                ? $r->diajukan_at->format('d/m/Y H:i')
                : '—')
            ->addColumn('status_badge', function ($r) {
                $map = [
                    'draft'       => ['secondary', 'Draft'],
                    'diajukan'    => ['info',      'Diajukan'],
                    'diverifikasi'=> ['primary',   'Diverifikasi'],
                    'ditolak'     => ['danger',    'Ditolak'],
                    'disetujui'   => ['success',   'Disetujui'],
                ];
                [$color, $label] = $map[$r->status] ?? ['secondary', $r->status];
                return "<span class='badge bg-{$color}'>{$label}</span>";
            })
            ->addColumn('action', function ($r) {
                $preview = "<a href='" . route('admin.akademik.raport.pengajuan.show', $r->id) . "' target='_blank' class='btn btn-xs btn-icon btn-light-secondary me-1' title='Preview'><i class='bi bi-eye'></i></a>";
                $verify  = "<button class='btn btn-xs btn-icon btn-light-info me-1' onclick='verifikasiRaport({$r->id})' title='Verifikasi'><i class='bi bi-patch-check'></i></button>";
                $reject  = "<button class='btn btn-xs btn-icon btn-light-danger' onclick='tolakRaport({$r->id})' title='Tolak'><i class='bi bi-x-circle'></i></button>";
                return $preview . $verify . $reject;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function verify(Request $request, int $id)
    {
        $catatan = $request->input('catatan_verifikasi');

        try {
            $this->service->verifikasi($id, $catatan, auth()->user()->id_user);
            return $this->response->success(null, 'Pengajuan berhasil diverifikasi.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function reject(Request $request, int $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:1000',
        ]);

        try {
            $this->service->tolak($id, $request->catatan);
            return $this->response->success(null, 'Pengajuan ditolak.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
