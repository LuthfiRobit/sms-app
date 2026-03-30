<?php

namespace App\Repositories\Peserta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Peserta\Peserta;

interface PesertaRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?Peserta;
    public function create(array $data): Peserta;
    public function update(int $id, array $data): Peserta;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    public function findByNisn(string $nisn): ?Peserta;
    public function findByNik(string $nik): ?Peserta;
    public function findByUserId(int $userId): ?Peserta;
}
