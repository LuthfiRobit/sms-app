<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\PengajuanIzinGuru;

class PengajuanIzinGuruRepository implements PengajuanIzinGuruRepositoryInterface
{
    public function __construct(protected PengajuanIzinGuru $model) {}

    public function datatable(array $filters = []): mixed
    {
        $query = $this->model->with(['guru:id,nama,gelar_depan,gelar_belakang,lembaga_id', 'lembaga:id,nama,kode'])
            ->latest('id');

        if (! empty($filters['lembaga_id'])) {
            $query->where('lembaga_id', $filters['lembaga_id']);
        }

        if (! empty($filters['guru_id'])) {
            $query->where('guru_id', $filters['guru_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    public function findById(int $id): ?PengajuanIzinGuru
    {
        return $this->model->with(['guru:id,nama,gelar_depan,gelar_belakang,lembaga_id', 'lembaga:id,nama,kode', 'diprosesOleh:id_user,name'])->find($id);
    }

    public function daftarMilikGuru(int $guruId): mixed
    {
        return $this->model->where('guru_id', $guruId)->latest('id')->get();
    }

    public function create(array $data): PengajuanIzinGuru
    {
        return $this->model->create($data);
    }

    public function update(PengajuanIzinGuru $row, array $data): PengajuanIzinGuru
    {
        $row->update($data);

        return $row;
    }

    public function hasOverlap(int $guruId, string $mulai, string $akhir, ?int $excludeId = null): bool
    {
        return $this->model
            ->where('guru_id', $guruId)
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where('tanggal_mulai', '<=', $akhir)
            ->where('tanggal_selesai', '>=', $mulai)
            ->exists();
    }
}
