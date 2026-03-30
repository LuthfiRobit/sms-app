<?php

namespace App\Services\Rbac;

use App\Repositories\Rbac\PermissionRepositoryInterface;
use App\Services\LogActivityService;
use Exception;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    protected $permissionRepository;
    protected $logActivityService;

    public function __construct(PermissionRepositoryInterface $permissionRepository, LogActivityService $logActivityService)
    {
        $this->permissionRepository = $permissionRepository;
        $this->logActivityService = $logActivityService;
    }

    public function createPermission(array $data)
    {
        DB::beginTransaction();
        try {
            $permission = $this->permissionRepository->create($data);
            $this->logActivityService->log('Create Permission', "Menambahkan Permission baru: {$permission->permission_name}");
            DB::commit();
            return $permission;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updatePermission(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $permission = $this->permissionRepository->update($id, $data);
            $this->logActivityService->log('Update Permission', "Memperbarui data Permission: {$permission->permission_name}");
            DB::commit();
            return $permission;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deletePermission(int $id)
    {
        DB::beginTransaction();
        try {
            $permission = $this->permissionRepository->getById($id);
            $permissionName = $permission->permission_name;
            $this->permissionRepository->delete($id);
            $this->logActivityService->log('Delete Permission', "Menghapus Permission: {$permissionName}");
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function toggleStatus(int $id)
    {
        DB::beginTransaction();
        try {
            $permission = $this->permissionRepository->getById($id);
            $newStatus = !$permission->is_active;
            $this->permissionRepository->update($id, ['is_active' => $newStatus]);

            $statusText = $newStatus ? 'Aktif' : 'Tidak Aktif';
            $this->logActivityService->log('Update Permission Status', "Mengubah status Permission {$permission->permission_name} menjadi {$statusText}");

            DB::commit();
            return $permission;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
