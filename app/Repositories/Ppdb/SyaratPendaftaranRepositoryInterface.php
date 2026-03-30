<?php

namespace App\Repositories\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ppdb\SyaratPendaftaran;

interface SyaratPendaftaranRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?SyaratPendaftaran;
    public function create(array $data): SyaratPendaftaran;
    public function update(int $id, array $data): SyaratPendaftaran;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

}
