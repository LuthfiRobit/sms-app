<?php

namespace App\Repositories\Rbac;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function getAll(): Collection
    {
        return User::all();
    }

    public function getById(int $id)
    {
        return User::findOrFail($id);
    }

    public function getDatatablesData()
    {
        return User::query(); // Usually includes specific eager loading if needed, like ->with('roles')
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update(int $id, array $data)
    {
        $user = $this->getById($id);
        $user->update($data);
        return $user;
    }

    public function delete(int $id)
    {
        $user = $this->getById($id);
        return $user->delete();
    }

    public function assignRoles(int $userId, array $roleIds)
    {
        $user = $this->getById($userId);
        return $user->roles()->sync($roleIds);
    }
}
