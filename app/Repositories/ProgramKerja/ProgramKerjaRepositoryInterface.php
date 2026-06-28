<?php

namespace App\Repositories\ProgramKerja;

use Illuminate\Database\Eloquent\Builder;

interface ProgramKerjaRepositoryInterface
{
    public function datatable(array $filters = []): Builder;
    public function findById(int $id, array $with = []): ?object;
    public function create(array $data): object;
    public function update(int $id, array $data): object;
    public function delete(int $id): void;

    // Kegiatan
    public function addKegiatan(int $programId, array $data): object;
    public function updateKegiatan(int $kegiatanId, array $data): object;
    public function deleteKegiatan(int $kegiatanId): void;
    public function updateRealisasi(int $kegiatanId, array $data): object;
    public function reorderKegiatan(int $programId, array $orderedIds): void;
}
