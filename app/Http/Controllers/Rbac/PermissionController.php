<?php

namespace App\Http\Controllers\Rbac;

use App\Http\Controllers\Controller;
use App\Repositories\Rbac\PermissionRepositoryInterface;
use App\Services\Rbac\PermissionService;
use App\Services\ResponseService;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    protected $permissionService;
    protected $permissionRepository;
    protected $responseService;
    protected $logActivityService;

    public function __construct(
        PermissionService $permissionService,
        PermissionRepositoryInterface $permissionRepository,
        ResponseService $responseService,
        LogActivityService $logActivityService
    ) {
        $this->permissionService = $permissionService;
        $this->permissionRepository = $permissionRepository;
        $this->responseService = $responseService;
        $this->logActivityService = $logActivityService;
    }

    public function index()
    {
        $this->logActivityService->log('Access Permission Index', 'Membuka halaman master permission');
        return view('admin.rbac.permission.index');
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $this->logActivityService->log('List Permissions', 'Mengambil data datatable permission');
            $data = $this->permissionRepository->getDatatablesData();

            if ($request->has('status') && $request->status != '') {
                $data->where('is_active', $request->status === 'active' ? 1 : 0);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function($row){
                    $badge = $row->is_active 
                        ? '<span class="badge bg-light-success">Aktif</span>' 
                        : '<span class="badge bg-light-danger">Non-Aktif</span>';
                    return $badge;
                })
                ->addColumn('action', function($row){
                    $btn = '<div class="btn-group">';
                    $btn .= '  <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    $btn .= '    <i class="bi bi-gear"></i> Aksi';
                    $btn .= '  </button>';
                    $btn .= '  <div class="dropdown-menu">';
                    if (auth()->user()->hasPermissionTo('admin.rbac.permission.show')) {
                        $btn .= '    <a class="dropdown-item btn-show" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-eye"></i> Lihat</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.rbac.permission.update')) {
                        $btn .= '    <a class="dropdown-item btn-edit" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-pencil"></i> Edit</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.rbac.permission.toggle')) {
                        $btn .= '    <div class="dropdown-divider"></div>';
                        $btn .= '    <a class="dropdown-item btn-toggle-status" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-power"></i> Ubah Status</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.rbac.permission.destroy')) {
                        $btn .= '    <a class="dropdown-item text-danger btn-delete" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-trash"></i> Hapus</a>';
                    }
                    $btn .= '  </div>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'permission_name' => 'required|string|max:255|unique:permissions,permission_name',
            'permission_description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            $data = $request->all();
            if(!isset($data['is_active'])) $data['is_active'] = true;
            
            $permission = $this->permissionService->createPermission($data);
            $this->logActivityService->log('Create Permission', 'Menambahkan permission ' . $permission->permission_name);
            return $this->responseService->success($permission, 'Permission berhasil ditambahkan');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menambahkan Permission: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $permission = $this->permissionRepository->getById($id);
            $this->logActivityService->log('Show Permission', 'Melihat detail permission ID ' . $id);
            return $this->responseService->success($permission);
        } catch (\Exception $e) {
            return $this->responseService->error('Permission tidak ditemukan');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'permission_name' => 'required|string|max:255|unique:permissions,permission_name,'.$id.',id',
            'permission_description' => 'nullable|string',
        ]);

        try {
            $permission = $this->permissionService->updatePermission($id, $request->only(['permission_name', 'permission_description']));
            $this->logActivityService->log('Update Permission', 'Memperbarui permission ' . $permission->permission_name);
            return $this->responseService->success($permission, 'Permission berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal memperbarui Permission: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $permission = $this->permissionService->toggleStatus($id);
            $this->logActivityService->log('Toggle Permission Status', 'Mengubah status permission ' . $permission->permission_name . ' menjadi ' . ($permission->is_active ? 'Aktif' : 'Non-Aktif'));
            return $this->responseService->success($permission, 'Status permission berhasil diubah');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal merubah status Permission: ' . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        try {
            $this->permissionService->deletePermission($id);
            $this->logActivityService->log('Delete Permission', 'Menghapus permission ID ' . $id);
            return $this->responseService->success(null, 'Permission berhasil dihapus');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menghapus Permission: ' . $e->getMessage());
        }
    }
}
