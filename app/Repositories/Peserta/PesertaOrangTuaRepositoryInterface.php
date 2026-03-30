<?php

namespace App\Repositories\Peserta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Peserta\PesertaOrangTua;

interface PesertaOrangTuaRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?PesertaOrangTua;
    public function create(array $data): PesertaOrangTua;
    public function update(int $id, array $data): PesertaOrangTua;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    public function findByPesertaIdAndTipe(int $pesertaId, string $tipe): ?PesertaOrangTua;
    public function upsertByTipe(int $pesertaId, string $tipe, array $data): PesertaOrangTua;
}
