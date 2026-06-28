<?php

namespace App\Repositories\Kinerja;

use App\Models\Kinerja\KpiIndikator;
use App\Models\Kinerja\KpiRealisasi;
use Illuminate\Support\Collection;

class KpiRepository implements KpiRepositoryInterface
{
    public function __construct(
        protected KpiIndikator $model,
        protected KpiRealisasi $realisasiModel,
    ) {}

    /**
     * Get all KPI indikator for a given lembaga and tahun pelajaran,
     * eager-loading latestRealisasi, ordered by kategori then urutan.
     */
    public function getByLembagaTahun(int $lembagaId, int $tahunId): Collection
    {
        return $this->model
            ->with(['latestRealisasi'])
            ->where('lembaga_id', $lembagaId)
            ->where('tahun_pelajaran_id', $tahunId)
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Find a single KPI indikator by its primary key.
     * Includes lembaga, tahunPelajaran, and full realisasi history (newest first).
     */
    public function findById(int $id, array $with = []): ?object
    {
        $defaultWith = [
            'lembaga',
            'tahunPelajaran',
            'realisasi' => fn ($q) => $q->orderBy('created_at', 'desc'),
        ];

        return $this->model->with(array_merge($defaultWith, $with))->find($id);
    }

    /**
     * Persist a new KPI indikator record.
     */
    public function createIndikator(array $data): object
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing KPI indikator record and return the refreshed model.
     */
    public function updateIndikator(int $id, array $data): object
    {
        $indikator = $this->model->findOrFail($id);
        $indikator->update($data);

        return $indikator->fresh();
    }

    /**
     * Delete a KPI indikator by its primary key.
     */
    public function deleteIndikator(int $id): void
    {
        $this->model->findOrFail($id)->delete();
    }

    /**
     * Insert or update a realisasi entry for a given indikator and periode.
     */
    public function upsertRealisasi(int $indikatorId, string $periode, float $nilai, ?string $catatan, ?int $userId): object
    {
        return KpiRealisasi::updateOrCreate(
            [
                'kpi_indikator_id' => $indikatorId,
                'periode'          => $periode,
            ],
            [
                'nilai_realisasi' => $nilai,
                'catatan'         => $catatan,
                'dicatat_oleh'    => $userId,
                'dicatat_at'      => now(),
            ]
        );
    }

    /**
     * Retrieve full realisasi history for a given indikator (newest first).
     */
    public function getRealisasiHistory(int $indikatorId): Collection
    {
        return KpiRealisasi::where('kpi_indikator_id', $indikatorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
