<?php

namespace App\Repositories\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ppdb\BiayaRegistrasi;

interface BiayaRegistrasiRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?BiayaRegistrasi;
    public function create(array $data): BiayaRegistrasi;
    public function update(int $id, array $data): BiayaRegistrasi;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

}
