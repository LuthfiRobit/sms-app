<?php

namespace App\Repositories\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ppdb\FormulirPendaftaran;

interface FormulirPendaftaranRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?FormulirPendaftaran;
    public function create(array $data): FormulirPendaftaran;
    public function update(int $id, array $data): FormulirPendaftaran;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    public function findWithFields(int $id): ?FormulirPendaftaran;
}
