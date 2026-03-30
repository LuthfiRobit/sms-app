<?php

namespace App\Repositories\Rbac;

use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(int $id);
    public function findByName(string $name);
    public function getDatatablesData();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function assignPermissions(int $roleId, array $permissionIds);
}
