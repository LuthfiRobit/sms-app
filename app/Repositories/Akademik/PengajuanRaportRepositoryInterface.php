<?php

namespace App\Repositories\Akademik;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface PengajuanRaportRepositoryInterface
{
    public function datatable(array $filters = []): Builder;

    public function findById(int $id, array $with = []): ?object;

    public function create(array $data): object;

    public function update(int $id, array $data): object;

    public function delete(int $id): void;

    public function getNilaiByPengajuan(int $pengajuanId): Collection;

    public function getAbsensiRekap(int $pengajuanId): Collection;

    public function upsertNilai(array $rows): void;

    public function upsertAbsensiRekap(array $rows): void;
}
