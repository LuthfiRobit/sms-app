<?php

namespace App\Repositories\Peserta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Peserta\PesertaPeriodik;

interface PesertaPeriodikRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?PesertaPeriodik;
    public function create(array $data): PesertaPeriodik;
    public function update(int $id, array $data): PesertaPeriodik;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

}
