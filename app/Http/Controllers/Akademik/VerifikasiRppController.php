<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Rpp;
use App\Models\Master\Guru;
use App\Models\Master\MataPelajaran;
use App\Services\Akademik\RppService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class VerifikasiRppController extends Controller
{
    public function __construct(
        protected RppService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Verifikasi RPP', 'Membuka halaman verifikasi RPP.');

        $activeLembagaId = app('active_lembaga_id');

        $guruList = Guru::when($activeLembagaId, fn ($q) => $q->where('lembaga_id', $activeLembagaId))->orderBy('nama')->get(['id', 'nama']);
        $mapelList = MataPelajaran::byLembaga($activeLembagaId)->aktif()->orderBy('urutan')->get(['id', 'nama']);

        return view('admin.akademik.verifikasi-rpp.index', compact('guruList', 'mapelList'));
    }

    public function list(Request $request)
    {
        $activeLembagaId = app('active_lembaga_id');

        $query = $this->service->datatable($activeLembagaId)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->guru_id, fn ($q) => $q->where('guru_id', $request->guru_id))
            ->when($request->mata_pelajaran_id, fn ($q) => $q->where('mata_pelajaran_id', $request->mata_pelajaran_id))
            ->latest();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('guru_nama', fn ($r) => $r->guru?->nama_lengkap ?? '—')
            ->addColumn('mapel_nama', fn ($r) => $r->mataPelajaran?->nama ?? '—')
            ->addColumn('status_badge', function ($r) {
                $badge = Rpp::statusBadge($r->status);

                return "<span class='badge bg-{$badge['class']}'>{$badge['label']}</span>";
            })
            ->addColumn('action', function ($r) {
                $detail = "<a href='".route('admin.akademik.rpp.show', $r->id)."' class='btn btn-xs btn-icon btn-light-info me-1' title='Lihat Detail'><i class='bi bi-eye'></i></a>";
                $approve = $r->status === 'pending'
                    ? "<button class='btn btn-xs btn-icon btn-light-success me-1' onclick='verifikasiRpp({$r->id},\"disetujui\")' title='Setujui'><i class='bi bi-check-lg'></i></button>"
                    : '';
                $reject = $r->status === 'pending'
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='verifikasiRpp({$r->id},\"ditolak\")' title='Tolak'><i class='bi bi-x-lg'></i></button>"
                    : '';

                return $detail.$approve.$reject;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function verifikasi(Request $request, int $id)
    {
        $data = $request->validate([
            'aksi' => 'required|in:disetujui,ditolak',
            'catatan_revisi' => 'nullable|string|max:1000',
        ]);

        try {
            $rpp = $this->service->verifikasi($id, $data['aksi'], $data['catatan_revisi'] ?? null);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        $label = $data['aksi'] === 'disetujui' ? 'disetujui' : 'ditolak';

        return $this->response->success($rpp, "RPP berhasil {$label}.");
    }
}
