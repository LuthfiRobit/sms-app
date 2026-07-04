<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\PengajuanIzinGuru;
use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Services\Akademik\PengajuanIzinGuruService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class PengajuanIzinGuruController extends Controller
{
    public function __construct(
        protected PengajuanIzinGuruService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Pengajuan Izin/Sakit Guru', 'Membuka halaman persetujuan izin/sakit guru.');

        $activeLembagaId = app('active_lembaga_id');
        $user = auth()->user();

        $lembagaList = Lembaga::orderBy('urutan')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('id', $user->getLembagaIds()))
            ->get(['id', 'nama', 'kode']);

        $guruList = Guru::when(! $user->isSuperAdmin(), fn ($q) => $q->where('lembaga_id', $activeLembagaId))
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'lembaga_id']);

        return view('admin.akademik.pengajuan-izin-guru.index', [
            'lembagaList' => $lembagaList,
            'guruList' => $guruList,
            'activeLembagaId' => $activeLembagaId,
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    public function list(Request $request)
    {
        $filters = [
            'lembaga_id' => $this->resolveLembagaId($request),
            'guru_id' => $request->integer('guru_id') ?: null,
            'status' => $request->input('status'),
        ];

        $query = $this->service->datatable(null, $filters);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('guru_nama', fn ($r) => $r->guru?->nama_lengkap ?? '—')
            ->addColumn('lembaga_nama', fn ($r) => $r->lembaga?->nama ?? '—')
            ->addColumn('jenis_label', fn ($r) => ucfirst($r->jenis))
            ->addColumn('tanggal_fmt', fn ($r) => $r->tanggal_mulai->format('d/m/Y').' – '.$r->tanggal_selesai->format('d/m/Y'))
            ->addColumn('status_badge', function ($r) {
                $badge = PengajuanIzinGuru::statusBadge($r->status);

                return "<span class='badge bg-{$badge['class']}'>{$badge['label']}</span>";
            })
            ->addColumn('action', function ($r) {
                if ($r->status !== 'menunggu' || ! auth()->user()->hasPermissionTo('admin.akademik.pengajuan-izin-guru.approve')) {
                    return "<button class='btn btn-xs btn-icon btn-light-info' onclick='lihatDetailIzin({$r->id})' title='Detail'><i class='bi bi-eye'></i></button>";
                }

                return "<button class='btn btn-xs btn-icon btn-light-info me-1' onclick='lihatDetailIzin({$r->id})' title='Detail'><i class='bi bi-eye'></i></button>"
                    ."<button class='btn btn-xs btn-icon btn-light-success me-1' onclick='setujuiIzin({$r->id})' title='Setujui'><i class='bi bi-check-lg'></i></button>"
                    ."<button class='btn btn-xs btn-icon btn-light-danger' onclick='tolakIzin({$r->id})' title='Tolak'><i class='bi bi-x-lg'></i></button>";
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function detail(int $id)
    {
        try {
            $row = $this->service->find($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        if (! $this->bolehAksesLembaga($row->lembaga_id)) {
            return $this->response->error('Anda tidak memiliki akses ke data lembaga ini.', 403);
        }

        $data = $row->toArray();
        $data['lampiran_url'] = $row->lampiran ? url('storage/'.$row->lampiran) : null;

        return $this->response->success($data, 'OK');
    }

    public function approve(Request $request, int $id)
    {
        $catatan = $request->input('catatan_admin');

        try {
            $row = $this->service->setujui($id, $catatan, auth()->id());
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success($row, 'Pengajuan berhasil disetujui.');
    }

    public function reject(Request $request, int $id)
    {
        $request->validate(['catatan_admin' => 'required|string|max:1000']);

        try {
            $row = $this->service->tolak($id, $request->input('catatan_admin'), auth()->id());
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success($row, 'Pengajuan ditolak.');
    }

    private function resolveLembagaId(Request $request): ?int
    {
        $requested = $request->integer('lembaga_id') ?: null;

        if (! $requested) {
            return app('active_lembaga_id');
        }

        return $this->bolehAksesLembaga($requested) ? $requested : app('active_lembaga_id');
    }

    private function bolehAksesLembaga(?int $lembagaId): bool
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $lembagaId && in_array($lembagaId, $user->getLembagaIds(), true);
    }
}
