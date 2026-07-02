<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\MateriBelajar;
use App\Models\Master\Guru;
use App\Models\Master\MataPelajaran;
use App\Repositories\Akademik\MateriBelajarRepositoryInterface;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VerifikasiMateriController extends Controller
{
    public function __construct(
        protected MateriBelajarRepositoryInterface $repo,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Verifikasi Materi', 'Membuka halaman verifikasi materi ajar.');

        $activeLembagaId = app('active_lembaga_id');

        $guruList  = Guru::when($activeLembagaId, fn($q) => $q->where('lembaga_id', $activeLembagaId))
            ->orderBy('nama')->get(['id', 'nama']);

        $mapelList = MataPelajaran::byLembaga($activeLembagaId)
            ->aktif()->orderBy('urutan')->get(['id', 'nama']);

        return view('admin.akademik.verifikasi-materi.index', compact('guruList', 'mapelList'));
    }

    public function list(Request $request)
    {
        $activeLembagaId = app('active_lembaga_id');

        $query = MateriBelajar::with(['guru', 'mataPelajaran', 'rombel', 'diverifikasiOleh'])
            ->when($activeLembagaId, fn($q) => $q->where('lembaga_id', $activeLembagaId))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->guru_id, fn($q) => $q->where('guru_id', $request->guru_id))
            ->when($request->mata_pelajaran_id, fn($q) => $q->where('mata_pelajaran_id', $request->mata_pelajaran_id))
            ->latest();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('guru_nama', fn($r) => $r->guru?->nama ?? '—')
            ->addColumn('mapel_nama', fn($r) => $r->mataPelajaran?->nama ?? '—')
            ->addColumn('tanggal_fmt', fn($r) => $r->tanggal?->format('d/m/Y') ?? '—')
            ->addColumn('status_badge', function ($r) {
                $badge = MateriBelajar::statusBadge($r->status);
                return "<span class='badge bg-{$badge['class']}'>{$badge['label']}</span>";
            })
            ->addColumn('action', function ($r) {
                $view = "<button class='btn btn-xs btn-icon btn-light-info me-1' onclick='viewMateri({$r->id})' title='Detail'><i class='bi bi-eye'></i></button>";
                $approve = $r->status === 'pending'
                    ? "<button class='btn btn-xs btn-icon btn-light-success me-1' onclick='verifikasiMateri({$r->id},\"disetujui\")' title='Setujui'><i class='bi bi-check-lg'></i></button>"
                    : '';
                $reject = $r->status === 'pending'
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='verifikasiMateri({$r->id},\"ditolak\")' title='Tolak'><i class='bi bi-x-lg'></i></button>"
                    : '';
                return $view . $approve . $reject;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /**
     * Approve or reject a materi.
     */
    public function verifikasi(Request $request, int $id)
    {
        $data = $request->validate([
            'aksi'           => 'required|in:disetujui,ditolak',
            'catatan_revisi' => 'nullable|string|max:1000',
        ]);

        $materi = MateriBelajar::findOrFail($id);

        if ($materi->status !== 'pending') {
            return $this->response->error('Materi ini sudah diverifikasi sebelumnya.');
        }

        $materi->update([
            'status'          => $data['aksi'],
            'catatan_revisi'  => $data['catatan_revisi'] ?? null,
            'diverifikasi_by' => auth()->user()->id_user,
            'diverifikasi_at' => now(),
        ]);

        $aksiLabel = $data['aksi'] === 'disetujui' ? 'menyetujui' : 'menolak';
        $this->logActivity->log(
            'Verifikasi Materi Ajar',
            "Admin {$aksiLabel} materi \"{$materi->judul}\" (ID #{$id})."
        );

        $label = $data['aksi'] === 'disetujui' ? 'disetujui' : 'ditolak';
        return $this->response->success($materi, "Materi berhasil {$label}.");
    }
}
