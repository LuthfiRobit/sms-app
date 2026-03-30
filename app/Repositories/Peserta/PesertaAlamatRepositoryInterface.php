<?php

namespace App\Repositories\Peserta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Peserta\PesertaAlamat;

interface PesertaAlamatRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?PesertaAlamat;
    public function create(array $data): PesertaAlamat;
    public function update(int $id, array $data): PesertaAlamat;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    public function findByPesertaId(int $pesertaId): ?PesertaAlamat;
    public function upsert(int $pesertaId, array $data): PesertaAlamat;
}
