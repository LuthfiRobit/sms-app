<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Master\LembagaRepositoryInterface;
use App\Services\Master\LembagaService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LembagaController extends Controller
{
    public function __construct(
        protected LembagaService $lembagaService,
        protected LembagaRepositoryInterface $lembagaRepository,
        protected ResponseService $responseService,
    ) {
    }

    public function index()
    {
        return view('admin.master.lembaga.index');
    }

    public function list(Request $request)
    {
        if (! $request->ajax()) {
            abort(403);
        }

        $data = $this->lembagaService->index();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('jenis_badge', fn ($row) => '<span class="badge bg-light-primary">'.$row->jenis.'</span>')
            ->addColumn('status', function ($row) {
                return $row->status === 'aktif'
                    ? '<span class="badge bg-light-success">Aktif</span>'
                    : '<span class="badge bg-light-danger">Non-Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '<button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">';
                $btn .= '<i class="bi bi-gear"></i> Aksi</button>';
                $btn .= '<div class="dropdown-menu">';
                if (auth()->user()->hasPermissionTo('admin.master.lembaga.show')) {
                    $btn .= '<a class="dropdown-item btn-show" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-eye"></i> Lihat</a>';
                }
                if (auth()->user()->hasPermissionTo('admin.master.lembaga.update')) {
                    $btn .= '<a class="dropdown-item btn-edit" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-pencil"></i> Edit</a>';
                }
                if (auth()->user()->hasPermissionTo('admin.master.lembaga.toggle')) {
                    $btn .= '<div class="dropdown-divider"></div>';
                    $btn .= '<a class="dropdown-item btn-toggle-status" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-arrow-repeat"></i> Toggle Aktif</a>';
                }
                if (auth()->user()->hasPermissionTo('admin.master.lembaga.destroy')) {
                    $btn .= '<a class="dropdown-item text-danger btn-delete" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-trash"></i> Hapus</a>';
                }
                $btn .= '</div></div>';
                return $btn;
            })
            ->rawColumns(['jenis_badge', 'status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode'           => 'required|string|max:20|unique:lembaga,kode',
            'nama'           => 'required|string|max:255',
            'npsn'           => 'nullable|string|max:20|unique:lembaga,npsn',
            'jenis'          => 'required|in:MI,MTs,SMP,MA,SMK',
            'alamat'         => 'nullable|string',
            'telepon'        => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'kepala_sekolah' => 'nullable|string|max:255',
            'logo'           => 'nullable|image|max:2048',
            'status'         => 'required|in:aktif,nonaktif',
            'urutan'         => 'nullable|integer',
        ]);

        try {
            $lembaga = $this->lembagaService->store($request->all());
            return $this->responseService->success($lembaga, 'Lembaga berhasil ditambahkan');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menambahkan Lembaga: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $lembaga = $this->lembagaRepository->findById((int) $id, ['users']);
            return $this->responseService->success($lembaga);
        } catch (\Exception $e) {
            return $this->responseService->error('Lembaga tidak ditemukan');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode'           => 'required|string|max:20|unique:lembaga,kode,'.$id,
            'nama'           => 'required|string|max:255',
            'npsn'           => 'nullable|string|max:20|unique:lembaga,npsn,'.$id,
            'jenis'          => 'required|in:MI,MTs,SMP,MA,SMK',
            'alamat'         => 'nullable|string',
            'telepon'        => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'kepala_sekolah' => 'nullable|string|max:255',
            'logo'           => 'nullable|image|max:2048',
            'status'         => 'required|in:aktif,nonaktif',
            'urutan'         => 'nullable|integer',
        ]);

        try {
            $lembaga = $this->lembagaService->update((int) $id, $request->all());
            return $this->responseService->success($lembaga, 'Lembaga berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal memperbarui Lembaga: '.$e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $lembaga = $this->lembagaService->toggleStatus((int) $id);
            return $this->responseService->success($lembaga, 'Status Lembaga berhasil diubah');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal mengubah status Lembaga: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->lembagaService->destroy((int) $id);
            return $this->responseService->success(null, 'Lembaga berhasil dihapus');
        } catch (\Exception $e) {
            return $this->responseService->error($e->getMessage());
        }
    }

    public function setAdmin(Request $request, $id)
    {
        $request->validate([
            'user_ids'   => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id_user',
        ]);

        try {
            $this->lembagaService->setAdmins((int) $id, $request->input('user_ids', []));
            return $this->responseService->success(null, 'Admin Lembaga berhasil diatur');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal mengatur admin: '.$e->getMessage());
        }
    }
}
