<?php

namespace App\Repositories\Peserta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Peserta\PesertaKontak;

interface PesertaKontakRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?PesertaKontak;
    public function create(array $data): PesertaKontak;
    public function update(int $id, array $data): PesertaKontak;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

}
