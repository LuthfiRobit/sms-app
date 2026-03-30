<?php

namespace App\Http\Controllers\Rbac;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Repositories\Rbac\RoleRepositoryInterface;
use App\Services\Rbac\RoleService;
use App\Services\ResponseService;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    protected $roleService;
    protected $roleRepository;
    protected $responseService;
    protected $logActivityService;

    public function __construct(
        RoleService $roleService,
        RoleRepositoryInterface $roleRepository,
        ResponseService $responseService,
        LogActivityService $logActivityService
    ) {
        $this->roleService = $roleService;
        $this->roleRepository = $roleRepository;
        $this->responseService = $responseService;
        $this->logActivityService = $logActivityService;
    }

    public function index()
    {
        $this->logActivityService->log('Access Role Index', 'Membuka halaman master role');
        return view('admin.rbac.role.index');
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $this->logActivityService->log('List Roles', 'Mengambil data datatable role');
            $data = $this->roleRepository->getDatatablesData();

            // Implement simple filter by scope if needed
            if ($request->has('scope') && $request->scope != '') {
                $data->where('scope', $request->scope);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '  <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    $btn .= '    <i class="bi bi-gear"></i> Aksi';
                    $btn .= '  </button>';
                    $btn .= '  <div class="dropdown-menu">';
                    if (auth()->user()->hasPermissionTo('admin.rbac.role.show')) {
                        $btn .= '    <a class="dropdown-item btn-show" href="javascript:void(0);" data-id="' . $row->id . '"><i class="bi bi-eye"></i> Lihat</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.rbac.role.update')) {
                        $btn .= '    <a class="dropdown-item btn-edit" href="javascript:void(0);" data-id="' . $row->id . '"><i class="bi bi-pencil"></i> Edit</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.rbac.role.permissions.list')) {
                        $btn .= '    <a class="dropdown-item" href="' . route('admin.rbac.role.edit', $row->id) . '"><i class="bi bi-key"></i> Permissions</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.rbac.role.destroy')) {
                        $btn .= '    <div class="dropdown-divider"></div>';
                        $btn .= '    <a class="dropdown-item text-danger btn-delete" href="javascript:void(0);" data-id="' . $row->id . '"><i class="bi bi-trash"></i> Hapus</a>';
                    }
                    $btn .= '  </div>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scope' => 'required|in:global,unit,class,subject,student,personal,process'
        ]);

        try {
            $role = $this->roleService->createRole($request->all());
            $this->logActivityService->log('Create Role', 'Menambahkan role ' . $role->name);
            return $this->responseService->success($role, 'Role berhasil ditambahkan');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menambahkan Role: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $role = $this->roleRepository->getById($id);
            $this->logActivityService->log('Show Role', 'Melihat detail role ID ' . $id);
            return $this->responseService->success($role);
        } catch (\Exception $e) {
            return $this->responseService->error('Role tidak ditemukan');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id . ',id',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scope' => 'required|in:global,unit,class,subject,student,personal,process'
        ]);

        try {
            $role = $this->roleService->updateRole($id, $request->all());
            $this->logActivityService->log('Update Role', 'Memperbarui role ' . $role->name);
            return $this->responseService->success($role, 'Role berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal memperbarui Role: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->roleService->deleteRole($id);
            $this->logActivityService->log('Delete Role', 'Menghapus role ID ' . $id);
            return $this->responseService->success(null, 'Role berhasil dihapus');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menghapus Role: ' . $e->getMessage());
        }
    }

    // Permission Management view for a specific role
    public function edit($id)
    {
        $role = $this->roleRepository->getById($id);
        $this->logActivityService->log('Edit Role Permissions View', 'Membuka halaman pengaturan permission untuk role ' . $role->name);

        // Passing permissions structure to view
        return view('admin.rbac.role.permission', compact('role'));
    }

    // API to get role permissions for checkboxes
    public function getPermissions($id)
    {
        try {
            $role = $this->roleRepository->getById($id);
            $this->logActivityService->log('Get Role Permissions', 'Mengambil data permissions untuk role ID ' . $id);
            $rolePermissions = $role->permissions->pluck('id')->toArray();

            // Format permissions into grouped array if necessary based on route name prefixes (e.g. admin.users.xxx)
            $allPermissions = Permission::where('is_active', true)->get();
            $groupedPermissions = [];

            foreach ($allPermissions as $perm) {
                $parts = explode('.', $perm->permission_name);
                // Standard: admin.category.module.action
                $category = isset($parts[1]) ? $parts[1] : 'general';
                $module = isset($parts[2]) ? $parts[2] : 'general';

                $groupedPermissions[$category][$module][] = [
                    'id' => $perm->id,
                    'name' => $perm->permission_name,
                    'description' => $perm->permission_description,
                    'checked' => in_array($perm->id, $rolePermissions)
                ];
            }


            return $this->responseService->success([
                'role' => $role,
                'permissions_grouped' => $groupedPermissions,
                'total_assigned' => count($rolePermissions)
            ]);
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal mengambil data permission role: ' . $e->getMessage());
        }
    }

    public function assignPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        try {
            $permissions = $request->permissions ?? [];
            $role = $this->roleService->assignPermissions($id, $permissions);
            $this->logActivityService->log('Assign Permissions', 'Memperbarui permissions untuk role ' . $role->name);
            return $this->responseService->success($role, 'Permissions berhasil diperbarui untuk Role ini');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal memperbarui permissions: ' . $e->getMessage());
        }
    }
}
