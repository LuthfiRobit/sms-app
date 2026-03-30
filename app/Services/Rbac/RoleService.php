<?php

namespace App\Services\Rbac;

use App\Repositories\Rbac\RoleRepositoryInterface;
use App\Services\LogActivityService;
use Exception;
use Illuminate\Support\Facades\DB;

class RoleService
{
    protected $roleRepository;
    protected $logActivityService;

    public function __construct(RoleRepositoryInterface $roleRepository, LogActivityService $logActivityService)
    {
        $this->roleRepository = $roleRepository;
        $this->logActivityService = $logActivityService;
    }

    public function createRole(array $data)
    {
        DB::beginTransaction();
        try {
            $role = $this->roleRepository->create($data);
            $this->logActivityService->log('Create Role', "Mendaftarkan Role baru: {$role->name}");
            DB::commit();
            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateRole(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $role = $this->roleRepository->update($id, $data);
            $this->logActivityService->log('Update Role', "Memperbarui data Role: {$role->name}");
            DB::commit();
            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteRole(int $id)
    {
        DB::beginTransaction();
        try {
            $role = $this->roleRepository->getById($id);
            $roleName = $role->name;
            $this->roleRepository->delete($id);
            $this->logActivityService->log('Delete Role', "Menghapus Role: {$roleName}");
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function assignPermissions(int $roleId, array $permissionIds)
    {
        DB::beginTransaction();
        try {
            $role = $this->roleRepository->getById($roleId);
            $this->roleRepository->assignPermissions($roleId, $permissionIds);
            $this->logActivityService->log('Assign Permissions to Role', "Mengelola izin untuk Role: {$role->name}");
            DB::commit();
            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
