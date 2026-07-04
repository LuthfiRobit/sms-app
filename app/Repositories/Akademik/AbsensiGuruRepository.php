<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\AbsensiGuru;

class AbsensiGuruRepository implements AbsensiGuruRepositoryInterface
{
    public function __construct(protected AbsensiGuru $model) {}

    public function findByGuruTanggal(int $guruId, string $tanggal): ?AbsensiGuru
    {
        return $this->model
            ->where('guru_id', $guruId)
            ->whereDate('tanggal', $tanggal)
            ->first();
    }

    public function create(array $data): AbsensiGuru
    {
        return $this->model->create($data);
    }

    public function update(AbsensiGuru $absensi, array $data): AbsensiGuru
    {
        $absensi->update($data);

        return $absensi;
    }

    public function datatable(array $filters = []): mixed
    {
        $query = $this->model->with([
            'guru:id,nama,gelar_depan,gelar_belakang,lembaga_id',
            'lembaga:id,nama,kode',
            'dikoreksiOleh:id_user,name',
        ]);

        if (! empty($filters['lembaga_id'])) {
            $query->where('lembaga_id', $filters['lembaga_id']);
        }

        if (! empty($filters['guru_id'])) {
            $query->where('guru_id', $filters['guru_id']);
        }

        if (! empty($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $filters['tanggal_mulai']);
        }

        if (! empty($filters['tanggal_akhir'])) {
            $query->whereDate('tanggal', '<=', $filters['tanggal_akhir']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('tanggal');
    }

    public function findById(int $id): ?AbsensiGuru
    {
        return $this->model->with([
            'guru:id,nama,gelar_depan,gelar_belakang,lembaga_id',
            'lembaga:id,nama,kode',
        ])->find($id);
    }

    /**
     * Timpa/buat baris absensi guru untuk tanggal tsb. Dipakai approval
     * izin/sakit (#4) supaya baris 'alpa' yang sudah dibuat auto-alpa job
     * ikut tertimpa jadi izin/sakit — sengaja updateOrCreate, bukan
     * firstOrCreate/skip.
     */
    public function updateOrCreateByGuruTanggal(int $guruId, int $lembagaId, string $tanggal, array $data): AbsensiGuru
    {
        return $this->model->updateOrCreate(
            ['guru_id' => $guruId, 'tanggal' => $tanggal],
            array_merge(['lembaga_id' => $lembagaId], $data)
        );
    }
}
