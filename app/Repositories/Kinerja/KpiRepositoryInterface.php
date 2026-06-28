<?php

namespace App\Repositories\Kinerja;

use Illuminate\Support\Collection;

interface KpiRepositoryInterface
{
    /**
     * Get all KPI indikator for a given lembaga and tahun pelajaran,
     * eager-loading latestRealisasi, ordered by kategori then urutan.
     */
    public function getByLembagaTahun(int $lembagaId, int $tahunId): Collection;

    /**
     * Find a single KPI indikator by its primary key with optional eager-loads.
     */
    public function findById(int $id, array $with = []): ?object;

    /**
     * Persist a new KPI indikator record.
     */
    public function createIndikator(array $data): object;

    /**
     * Update an existing KPI indikator record.
     */
    public function updateIndikator(int $id, array $data): object;

    /**
     * Delete a KPI indikator by its primary key.
     */
    public function deleteIndikator(int $id): void;

    /**
     * Insert or update a realisasi entry for a given indikator and periode.
     */
    public function upsertRealisasi(int $indikatorId, string $periode, float $nilai, ?string $catatan, ?int $userId): object;

    /**
     * Retrieve full realisasi history for a given indikator (newest first).
     */
    public function getRealisasiHistory(int $indikatorId): Collection;
}
