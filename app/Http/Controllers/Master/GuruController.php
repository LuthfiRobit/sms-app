<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Repositories\Master\GuruRepositoryInterface;
use App\Services\Master\GuruService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class GuruController extends Controller
{
    public function __construct(
        protected GuruRepositoryInterface $repo,
        protected GuruService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Guru', 'Membuka halaman manajemen data guru.');
        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);
        return view('admin.master.guru.index', compact('lembagaList'));
    }

    public function list()
    {
        $lembagaId = app('active_lembaga_id');
        $query = $this->service->datatable($lembagaId);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('lembaga_nama', fn ($g) => $g->lembaga?->nama ?? '<span class="text-muted">—</span>')
            ->addColumn('nama_lengkap', fn ($g) => $g->nama_lengkap)
            ->addColumn('jenis_kelamin_label', fn ($g) => match ($g->jenis_kelamin) {
                'L' => '<span class="badge bg-light-primary text-primary">Laki-laki</span>',
                'P' => '<span class="badge bg-light-danger text-danger">Perempuan</span>',
                default => '<span class="text-muted">—</span>',
            })
            ->addColumn('status_badge', fn ($g) => $g->status === 'aktif'
                ? '<span class="badge bg-light-success text-success">Aktif</span>'
                : '<span class="badge bg-light-secondary text-secondary">Non-Aktif</span>')
            ->addColumn('action', function ($g) {
                $namaJs = addslashes($g->nama_lengkap);
                $edit = auth()->user()->hasPermissionTo('admin.master.guru.update')
                    ? "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editGuru({$g->id})' title='Edit'><i class='bi bi-pencil'></i></button>"
                    : '';
                $del = auth()->user()->hasPermissionTo('admin.master.guru.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusGuru({$g->id},\"{$namaJs}\")' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';
                return $edit . $del;
            })
            ->rawColumns(['lembaga_nama', 'jenis_kelamin_label', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lembaga_id'     => 'required|exists:lembaga,id',
            'nip'            => 'nullable|string|max:20',
            'nuptk'          => 'nullable|string|max:16',
            'nama'           => 'required|string|max:255',
            'gelar_depan'    => 'nullable|string|max:50',
            'gelar_belakang' => 'nullable|string|max:50',
            'jenis_kelamin'  => 'nullable|in:L,P',
            'status'         => 'required|in:aktif,nonaktif',
        ]);

        $guru = $this->service->store($data);
        return $this->response->success('Guru berhasil ditambahkan.', $guru);
    }

    public function show(int $id)
    {
        $guru = $this->repo->findById($id);
        if (! $guru) {
            return $this->response->error('Guru tidak ditemukan.', 404);
        }
        return $this->response->success('', $guru);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'lembaga_id'     => 'required|exists:lembaga,id',
            'nip'            => 'nullable|string|max:20',
            'nuptk'          => 'nullable|string|max:16',
            'nama'           => 'required|string|max:255',
            'gelar_depan'    => 'nullable|string|max:50',
            'gelar_belakang' => 'nullable|string|max:50',
            'jenis_kelamin'  => 'nullable|in:L,P',
            'status'         => 'required|in:aktif,nonaktif',
        ]);

        $guru = $this->service->update($id, $data);
        return $this->response->success('Data guru berhasil diperbarui.', $guru);
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
            return $this->response->success('Guru berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
