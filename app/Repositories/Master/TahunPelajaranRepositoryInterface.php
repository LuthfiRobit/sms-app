<?php

namespace App\Repositories\Master;

use App\Models\Master\TahunPelajaran;
use Illuminate\Database\Eloquent\Collection;

interface TahunPelajaranRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(int $id): ?TahunPelajaran;
    public function getDatatablesData();
    public function create(array $data): TahunPelajaran;
    public function update(int $id, array $data): TahunPelajaran;
    public function delete(int $id): bool;
}
