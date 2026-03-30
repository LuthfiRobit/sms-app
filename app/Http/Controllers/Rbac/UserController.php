<?php

namespace App\Http\Controllers\Rbac;

use App\Http\Controllers\Controller;
use App\Repositories\Rbac\RoleRepositoryInterface;
use App\Repositories\Rbac\UserRepositoryInterface;
use App\Services\Rbac\UserService;
use App\Services\ResponseService;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    protected $userService;
    protected $userRepository;
    protected $roleRepository;
    protected $responseService;
    protected $logActivityService;

    public function __construct(
        UserService $userService,
        UserRepositoryInterface $userRepository,
        RoleRepositoryInterface $roleRepository,
        ResponseService $responseService,
        LogActivityService $logActivityService
    ) {
        $this->userService = $userService;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->responseService = $responseService;
        $this->logActivityService = $logActivityService;
    }

    public function index()
    {
        $this->logActivityService->log('Access User Index', 'Membuka halaman master user');
        $roles = $this->roleRepository->getAll();
        return view('admin.rbac.user.index', compact('roles'));
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $this->logActivityService->log('List Users', 'Mengambil data datatable user');
            $data = $this->userRepository->getDatatablesData()->with('roles');

            if ($request->has('status') && $request->status != '') {
                $data->where('status', $request->status);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('roles', function($row){
                    return $row->roles->pluck('display_name')->implode(', ');
                })
                ->addColumn('status', function($row){
                    $badge = $row->status == 'active' 
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
                    if (auth()->user()->hasPermissionTo('admin.rbac.user.show')) {
                        $btn .= '    <a class="dropdown-item btn-show" href="javascript:void(0);" data-id="'.$row->id_user.'"><i class="bi bi-eye"></i> Lihat</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.rbac.user.update')) {
                        $btn .= '    <a class="dropdown-item btn-edit" href="javascript:void(0);" data-id="'.$row->id_user.'"><i class="bi bi-pencil"></i> Edit</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.rbac.user.toggle')) {
                        $btn .= '    <div class="dropdown-divider"></div>';
                        $btn .= '    <a class="dropdown-item btn-toggle-status" href="javascript:void(0);" data-id="'.$row->id_user.'"><i class="bi bi-lock"></i> Toggle Aktif</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.rbac.user.destroy')) {
                        $btn .= '    <a class="dropdown-item text-danger btn-delete" href="javascript:void(0);" data-id="'.$row->id_user.'"><i class="bi bi-trash"></i> Hapus</a>';
                    }
                    $btn .= '  </div>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['roles', 'status', 'action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'status' => 'required|in:active,inactive',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        try {
            $user = $this->userService->createUser($request->only(['name', 'username', 'email', 'password', 'status']));
            
            if ($request->has('roles')) {
                $this->userService->assignRoles($user->id_user, $request->roles);
            }
            
            $this->logActivityService->log('Create User', 'Menambahkan user ' . $user->username);
            return $this->responseService->success($user, 'User berhasil ditambahkan');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menambahkan User: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $user = $this->userRepository->getById($id);
            $this->logActivityService->log('Show User', 'Melihat detail user ID ' . $id);
            $user->load('roles');
            return $this->responseService->success([
                'user' => $user,
                'role_ids' => $user->roles->pluck('id')->toArray()
            ]);
        } catch (\Exception $e) {
            return $this->responseService->error('User tidak ditemukan');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$id.',id_user',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id.',id_user',
            'password' => 'nullable|string|min:6', // optional
            'status' => 'required|in:active,inactive',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        try {
            $userData = $request->only(['name', 'username', 'email', 'status']);
            if ($request->filled('password')) {
                $userData['password'] = $request->password;
            }

            $user = $this->userService->updateUser($id, $userData);
            
            $roles = $request->roles ?? [];
            $this->userService->assignRoles($id, $roles);
            
            $this->logActivityService->log('Update User', 'Memperbarui user ' . $user->username);
            return $this->responseService->success($user, 'User berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal memperbarui User: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $user = $this->userService->toggleStatus($id);
            $this->logActivityService->log('Toggle User Status', 'Mengubah status user ' . $user->username . ' menjadi ' . ($user->status == 'active' ? 'Aktif' : 'Non-Aktif'));
            return $this->responseService->success($user, 'Status user berhasil diubah');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal merubah status User: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->userService->deleteUser($id);
            $this->logActivityService->log('Delete User', 'Menghapus user ID ' . $id);
            return $this->responseService->success(null, 'User berhasil dihapus');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menghapus User: ' . $e->getMessage());
        }
    }
}
