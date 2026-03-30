<?php

namespace App\Repositories\Rbac;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RoleRepository implements RoleRepositoryInterface
{
    public function getAll(): Collection
    {
        return Role::all();
    }

    public function getById(int $id)
    {
        return Role::findOrFail($id);
    }

    public function findByName(string $name)
    {
        return Role::where('name', $name)->first();
    }

    public function getDatatablesData()
    {
        return Role::query();
    }

    public function create(array $data)
    {
        return Role::create($data);
    }

    public function update(int $id, array $data)
    {
        $role = $this->getById($id);
        $role->update($data);
        return $role;
    }

    public function delete(int $id)
    {
        $role = $this->getById($id);
        return $role->delete();
    }

    public function assignPermissions(int $roleId, array $permissionIds)
    {
        $role = $this->getById($roleId);
        return $role->permissions()->sync($permissionIds);
    }
}
