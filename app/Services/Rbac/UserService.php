<?php

namespace App\Services\Rbac;

use App\Repositories\Rbac\UserRepositoryInterface;
use App\Services\LogActivityService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $userRepository;
    protected $logActivityService;

    public function __construct(UserRepositoryInterface $userRepository, LogActivityService $logActivityService)
    {
        $this->userRepository = $userRepository;
        $this->logActivityService = $logActivityService;
    }

    public function createUser(array $data)
    {
        DB::beginTransaction();
        try {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user = $this->userRepository->create($data);
            $this->logActivityService->log('Create User', "Mendaftarkan User baru: {$user->username}");
            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateUser(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user = $this->userRepository->update($id, $data);
            $this->logActivityService->log('Update User', "Memperbarui data User: {$user->username}");
            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteUser(int $id)
    {
        DB::beginTransaction();
        try {
            $user = $this->userRepository->getById($id);
            $username = $user->username;
            $this->userRepository->delete($id);
            $this->logActivityService->log('Delete User', "Menghapus User: {$username}");
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
            $user = $this->userRepository->getById($id);
            $newStatus = $user->status === 'active' ? 'inactive' : 'active';
            $this->userRepository->update($id, ['status' => $newStatus]);

            $this->logActivityService->log('Update User Status', "Mengubah status User {$user->username} menjadi {$newStatus}");

            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function assignRoles(int $userId, array $roleIds)
    {
        DB::beginTransaction();
        try {
            $user = $this->userRepository->getById($userId);
            $this->userRepository->assignRoles($userId, $roleIds);
            $this->logActivityService->log('Assign Roles to User', "Mengelola Role untuk User: {$user->username}");
            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
