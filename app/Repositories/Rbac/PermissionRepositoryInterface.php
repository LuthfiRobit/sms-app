<?php

namespace App\Repositories\Rbac;

use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    public function getAll(): Collection;
    public function getActive(): Collection;
    public function getById(int $id);
    public function getDatatablesData();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
