<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\KalenderLibur;

interface KalenderLiburRepositoryInterface
{
    public function datatable(array $filters = []): mixed;

    public function findById(int $id): ?KalenderLibur;

    public function findExisting(string $tanggal, ?int $lembagaId): ?KalenderLibur;

    public function existingDatesInRange(string $mulai, string $akhir, ?int $lembagaId): array;

    public function create(array $data): KalenderLibur;

    public function createMany(array $rows): int;

    public function update(KalenderLibur $row, array $data): KalenderLibur;

    public function delete(KalenderLibur $row): bool;
}
