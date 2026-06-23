<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Repositories\Master\MataPelajaranRepositoryInterface;
use App\Services\Master\MataPelajaranService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MataPelajaranController extends Controller
{
    public function __construct(
        protected MataPelajaranRepositoryInterface $repo,
        protected MataPelajaranService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Mata Pelajaran', 'Membuka halaman manajemen mata pelajaran.');
        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);
        return view('admin.master.mata-pelajaran.index', compact('lembagaList'));
    }

    public function list()
    {
        $lembagaId = app('active_lembaga_id');
        $query = $this->service->datatable($lembagaId);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('lembaga_nama', fn ($r) => $r->lembaga?->nama ?? '<span class="text-muted">Semua Lembaga</span>')
            ->addColumn('kelompok_badge', fn ($r) => match ($r->kelompok) {
                'wajib'     => '<span class="badge bg-light-primary text-primary">Wajib</span>',
                'peminatan' => '<span class="badge bg-light-success text-success">Peminatan</span>',
                'mulok'     => '<span class="badge bg-light-warning text-warning">Mulok</span>',
                default     => '<span class="badge bg-light-secondary">—</span>',
            })
            ->addColumn('status_badge', fn ($r) => $r->status === 'aktif'
                ? '<span class="badge bg-light-success text-success">Aktif</span>'
                : '<span class="badge bg-light-secondary text-secondary">Non-Aktif</span>')
            ->addColumn('action', function ($r) {
                $namaJs = addslashes($r->nama);
                $edit = auth()->user()->hasPermissionTo('admin.master.mata-pelajaran.update')
                    ? "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editMapel({$r->id})' title='Edit'><i class='bi bi-pencil'></i></button>"
                    : '';
                $del = auth()->user()->hasPermissionTo('admin.master.mata-pelajaran.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusMapel({$r->id},\"{$namaJs}\")' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';
                return $edit . $del;
            })
            ->rawColumns(['lembaga_nama', 'kelompok_badge', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lembaga_id' => 'nullable|exists:lembaga,id',
            'kode'       => 'required|string|max:20',
            'nama'       => 'required|string|max:255',
            'kelompok'   => 'required|in:wajib,peminatan,mulok',
            'status'     => 'required|in:aktif,nonaktif',
            'urutan'     => 'nullable|integer|min:0',
        ]);

        $mapel = $this->service->store($data);
        return $this->response->success('Mata pelajaran berhasil ditambahkan.', $mapel);
    }

    public function show(int $id)
    {
        $mapel = $this->repo->findById($id, ['lembaga']);
        if (!$mapel) {
            return $this->response->error('Mata pelajaran tidak ditemukan.', 404);
        }
        return $this->response->success('', $mapel);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'lembaga_id' => 'nullable|exists:lembaga,id',
            'kode'       => 'required|string|max:20',
            'nama'       => 'required|string|max:255',
            'kelompok'   => 'required|in:wajib,peminatan,mulok',
            'status'     => 'required|in:aktif,nonaktif',
            'urutan'     => 'nullable|integer|min:0',
        ]);

        $mapel = $this->service->update($id, $data);
        return $this->response->success('Mata pelajaran berhasil diperbarui.', $mapel);
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
            return $this->response->success('Mata pelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
