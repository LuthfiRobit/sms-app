<?php

namespace App\Repositories\ProgramKerja;

use App\Models\ProgramKerja\KegiatanProgramKerja;
use App\Models\ProgramKerja\ProgramKerja;
use Illuminate\Database\Eloquent\Builder;

class ProgramKerjaRepository implements ProgramKerjaRepositoryInterface
{
    public function __construct(
        protected ProgramKerja $model,
        protected KegiatanProgramKerja $kegiatanModel
    ) {}

    public function datatable(array $filters = []): Builder
    {
        $query = $this->model
            ->with(['lembaga:id,nama', 'tahunPelajaran:id,nama']);

        if (! empty($filters['lembaga_id'])) {
            $query->where('lembaga_id', $filters['lembaga_id']);
        }

        if (! empty($filters['tahun_pelajaran_id'])) {
            $query->where('tahun_pelajaran_id', $filters['tahun_pelajaran_id']);
        }

        if (! empty($filters['bidang'])) {
            $query->where('bidang', $filters['bidang']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    public function findById(int $id, array $with = []): ?object
    {
        if (empty($with)) {
            $with = [
                'lembaga',
                'tahunPelajaran',
                'kegiatan' => function ($q) {
                    $q->orderBy('urutan')->orderBy('nama_kegiatan');
                },
            ];
        }

        return $this->model->with($with)->find($id);
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): object
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    public function delete(int $id): void
    {
        ProgramKerja::findOrFail($id)->delete();
    }

    // =========================================================================
    // Kegiatan
    // =========================================================================

    public function addKegiatan(int $programId, array $data): object
    {
        return KegiatanProgramKerja::create([
            'program_kerja_id' => $programId,
            ...$data,
        ]);
    }

    public function updateKegiatan(int $kegiatanId, array $data): object
    {
        $kegiatan = KegiatanProgramKerja::findOrFail($kegiatanId);
        $kegiatan->update($data);

        return $kegiatan->fresh();
    }

    public function deleteKegiatan(int $kegiatanId): void
    {
        KegiatanProgramKerja::findOrFail($kegiatanId)->delete();
    }

    public function updateRealisasi(int $kegiatanId, array $data): object
    {
        $kegiatan = KegiatanProgramKerja::findOrFail($kegiatanId);
        $kegiatan->update($data);

        return $kegiatan->fresh();
    }

    public function reorderKegiatan(int $programId, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            KegiatanProgramKerja::where('id', $id)
                ->where('program_kerja_id', $programId)
                ->update(['urutan' => $index + 1]);
        }
    }
}
