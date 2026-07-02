<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\Rombel;
use App\Models\Master\TahunPelajaran;
use App\Repositories\Akademik\MateriBelajarRepositoryInterface;
use App\Services\Akademik\MateriBelajarService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MateriBelajarController extends Controller
{
    public function __construct(
        protected MateriBelajarRepositoryInterface $repo,
        protected MateriBelajarService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Materi Belajar', 'Membuka halaman manajemen materi belajar.');

        $activeLembagaId = app('active_lembaga_id');

        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);

        $guruList = Guru::when($activeLembagaId, fn($q) => $q->where('lembaga_id', $activeLembagaId))
            ->orderBy('nama')
            ->get(['id', 'lembaga_id', 'nama', 'gelar_depan', 'gelar_belakang']);

        $mapelList = MataPelajaran::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('urutan')
            ->get(['id', 'lembaga_id', 'nama']);

        $rombelList = Rombel::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'lembaga_id', 'nama']);

        $tahunList = TahunPelajaran::orderBy('nama', 'desc')->get(['id', 'nama']);

        return view('admin.akademik.materi-belajar.index', compact(
            'lembagaList',
            'guruList',
            'mapelList',
            'rombelList',
            'tahunList',
        ));
    }

    public function list()
    {
        $activeLembagaId = app('active_lembaga_id');
        $query = $this->service->datatable($activeLembagaId);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('guru_nama', fn($r) => $r->guru?->nama_lengkap ?? '—')
            ->addColumn('mapel_nama', fn($r) => $r->mataPelajaran?->nama ?? '—')
            ->addColumn('rombel_nama', fn($r) => $r->rombel?->nama ?? 'Semua Rombel')
            ->addColumn('tanggal_fmt', fn($r) => $r->tanggal ? $r->tanggal->format('d/m/Y') : '—')
            ->addColumn('file_link', function ($r) {
                $parts = [];
                if ($r->file_path) {
                    $url = asset('storage/' . $r->file_path);
                    $name = e($r->file_name ?? 'Unduh File');
                    $parts[] = "<a href='{$url}' target='_blank' class='btn btn-xs btn-light-info mb-1' title='{$name}'><i class='bi bi-file-earmark me-1'></i>File</a>";
                }
                if ($r->url_eksternal) {
                    $url = e($r->url_eksternal);
                    $parts[] = "<a href='{$url}' target='_blank' class='btn btn-xs btn-light-primary mb-1'><i class='bi bi-link-45deg me-1'></i>Link</a>";
                }
                return $parts ? implode(' ', $parts) : '<span class="text-muted small">—</span>';
            })
            ->addColumn('status_badge', function ($r) {
                $badge = \App\Models\Akademik\MateriBelajar::statusBadge($r->status);
                return "<span class='badge bg-{$badge['class']}'>{$badge['label']}</span>";
            })
            ->addColumn('action', function ($r) {
                $edit = auth()->user()->hasPermissionTo('admin.akademik.materi-belajar.update')
                    ? "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editMateri({$r->id})' title='Edit'><i class='bi bi-pencil'></i></button>"
                    : '';
                $judul = addslashes($r->judul);
                $del = auth()->user()->hasPermissionTo('admin.akademik.materi-belajar.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusMateri({$r->id},\"{$judul}\")' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';
                return $edit . $del;
            })
            ->rawColumns(['file_link', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lembaga_id'        => 'required|exists:lembaga,id',
            'guru_id'           => 'required|exists:guru,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'rombel_id'         => 'nullable|exists:rombel,id',
            'tahun_pelajaran_id'=> 'required|exists:tahun_pelajaran,id',
            'judul'             => 'required|string|max:255',
            'deskripsi'         => 'nullable|string',
            'file'              => 'nullable|file|max:20480',
            'url_eksternal'     => 'nullable|url|max:500',
            'tanggal'           => 'required|date',
            'status'            => 'nullable|in:pending,disetujui,ditolak',
        ]);

        // Materi yang baru diupload oleh guru langsung masuk status pending untuk diverifikasi
        $data['status'] = 'pending';

        $file = $request->file('file');
        unset($data['file']);

        $record = $this->service->store($data, $file);
        return $this->response->success($record, 'Materi belajar berhasil ditambahkan.');
    }

    public function show(int $id)
    {
        $record = $this->repo->findById($id);
        if (!$record) {
            return $this->response->error('Data tidak ditemukan.', 404);
        }
        return $this->response->success($record, '');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'lembaga_id'        => 'required|exists:lembaga,id',
            'guru_id'           => 'required|exists:guru,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'rombel_id'         => 'nullable|exists:rombel,id',
            'tahun_pelajaran_id'=> 'required|exists:tahun_pelajaran,id',
            'judul'             => 'required|string|max:255',
            'deskripsi'         => 'nullable|string',
            'file'              => 'nullable|file|max:20480',
            'url_eksternal'     => 'nullable|url|max:500',
            'tanggal'           => 'required|date',
        ]);

        // Editing a materi resets it to pending for re-verification
        $data['status'] = 'pending';
        $data['catatan_revisi']  = null;
        $data['diverifikasi_by'] = null;
        $data['diverifikasi_at'] = null;

        $file = $request->file('file');
        unset($data['file']);

        $record = $this->service->update($id, $data, $file);
        return $this->response->success($record, 'Materi belajar berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
            return $this->response->success(null, 'Materi belajar berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
