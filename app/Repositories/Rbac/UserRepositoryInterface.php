<?php

namespace App\Repositories\Rbac;

use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(int $id);
    public function getDatatablesData();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function assignRoles(int $userId, array $roleIds);
}
