<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Repositories\Master\JurusanRepositoryInterface;
use App\Services\Master\JurusanService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class JurusanController extends Controller
{
    public function __construct(
        protected JurusanRepositoryInterface $repo,
        protected JurusanService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Jurusan', 'Membuka halaman manajemen jurusan / program studi.');
        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);
        return view('admin.master.jurusan.index', compact('lembagaList'));
    }

    public function list()
    {
        $activeLembagaId = app('active_lembaga_id');
        $query = $this->service->datatable($activeLembagaId);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('lembaga_nama', fn($r) => $r->lembaga?->nama ?? '—')
            ->addColumn('lembaga_kode', fn($r) => $r->lembaga?->kode ?? '—')
            ->addColumn('status_badge', fn($r) => $r->status === 'aktif'
                ? '<span class="badge bg-light-success text-success">Aktif</span>'
                : '<span class="badge bg-light-secondary text-secondary">Non-Aktif</span>')
            ->addColumn('action', function ($r) {
                $edit = auth()->user()->hasPermissionTo('admin.master.jurusan.update')
                    ? "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editJurusan({$r->id})' title='Edit'><i class='bi bi-pencil'></i></button>"
                    : '';
                $namaJs = addslashes($r->nama);
                $del = auth()->user()->hasPermissionTo('admin.master.jurusan.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusJurusan({$r->id},\"{$namaJs}\")' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';
                return $edit . $del;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lembaga_id' => 'required|exists:lembaga,id',
            'kode'       => 'required|string|max:30|unique:jurusan,kode',
            'nama'       => 'required|string|max:255',
            'deskripsi'  => 'nullable|string|max:500',
            'status'     => 'required|in:aktif,nonaktif',
            'urutan'     => 'nullable|integer|min:0',
        ]);

        $jurusan = $this->service->store($data);
        return $this->response->success('Jurusan berhasil ditambahkan.', $jurusan);
    }

    public function show(int $id)
    {
        $jurusan = $this->repo->findById($id, ['lembaga']);
        if (!$jurusan) {
            return $this->response->error('Jurusan tidak ditemukan.', 404);
        }
        return $this->response->success('', $jurusan);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'lembaga_id' => 'required|exists:lembaga,id',
            'kode'       => "required|string|max:30|unique:jurusan,kode,{$id}",
            'nama'       => 'required|string|max:255',
            'deskripsi'  => 'nullable|string|max:500',
            'status'     => 'required|in:aktif,nonaktif',
            'urutan'     => 'nullable|integer|min:0',
        ]);

        $jurusan = $this->service->update($id, $data);
        return $this->response->success('Jurusan berhasil diperbarui.', $jurusan);
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
            return $this->response->success('Jurusan berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
