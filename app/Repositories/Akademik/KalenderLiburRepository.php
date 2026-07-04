<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\KalenderLibur;

class KalenderLiburRepository implements KalenderLiburRepositoryInterface
{
    public function __construct(protected KalenderLibur $model) {}

    public function datatable(array $filters = []): mixed
    {
        $query = $this->model->query()->with('lembaga:id,nama,kode')->orderByDesc('tanggal');

        if (array_key_exists('lembaga_id', $filters) && $filters['lembaga_id']) {
            $lembagaId = $filters['lembaga_id'];
            $query->where(fn ($q) => $q->whereNull('lembaga_id')->orWhere('lembaga_id', $lembagaId));
        }

        if (! empty($filters['tahun'])) {
            $query->whereYear('tanggal', $filters['tahun']);
        }

        return $query;
    }

    public function findById(int $id): ?KalenderLibur
    {
        return $this->model->with('lembaga:id,nama,kode')->find($id);
    }

    public function findExisting(string $tanggal, ?int $lembagaId): ?KalenderLibur
    {
        return $this->model
            ->whereDate('tanggal', $tanggal)
            ->where('lembaga_id', $lembagaId)
            ->first();
    }

    public function existingDatesInRange(string $mulai, string $akhir, ?int $lembagaId): array
    {
        return $this->model
            ->whereBetween('tanggal', [$mulai, $akhir])
            ->where(fn ($q) => $q->whereNull('lembaga_id')->orWhere('lembaga_id', $lembagaId))
            ->pluck('tanggal')
            ->map(fn ($t) => $t->toDateString())
            ->all();
    }

    public function create(array $data): KalenderLibur
    {
        return $this->model->create($data);
    }

    public function createMany(array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $this->model->insert($rows);

        return count($rows);
    }

    public function update(KalenderLibur $row, array $data): KalenderLibur
    {
        $row->update($data);

        return $row;
    }

    public function delete(KalenderLibur $row): bool
    {
        return (bool) $row->delete();
    }
}
