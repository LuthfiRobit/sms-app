<?php

namespace App\Repositories\Master;

use App\Models\Master\Semester;
use Illuminate\Database\Eloquent\Collection;

interface SemesterRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(int $id): ?Semester;
    public function getDatatablesData();
    public function create(array $data): Semester;
    public function update(int $id, array $data): Semester;
    public function delete(int $id): bool;
}
