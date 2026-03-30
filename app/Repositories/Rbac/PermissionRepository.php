<?php

namespace App\Repositories\Rbac;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function getAll(): Collection
    {
        return Permission::all();
    }

    public function getActive(): Collection
    {
        return Permission::where('is_active', true)->get();
    }

    public function getById(int $id)
    {
        return Permission::findOrFail($id);
    }

    public function getDatatablesData()
    {
        return Permission::query();
    }

    public function create(array $data)
    {
        return Permission::create($data);
    }

    public function update(int $id, array $data)
    {
        $permission = $this->getById($id);
        $permission->update($data);
        return $permission;
    }

    public function delete(int $id)
    {
        $permission = $this->getById($id);
        return $permission->delete();
    }
}
