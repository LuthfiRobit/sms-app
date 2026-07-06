<?php

namespace App\Services\Master;

use App\Models\Master\ModelPembelajaranSintaks;
use App\Repositories\Master\ModelPembelajaranRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ModelPembelajaranService
{
    public function __construct(
        protected ModelPembelajaranRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    public function datatable(): mixed
    {
        return $this->repo->datatable();
    }

    public function find(int $id): object
    {
        $model = $this->repo->findById($id);

        if (! $model) {
            throw new RuntimeException('Model Pembelajaran tidak ditemukan.');
        }

        return $model;
    }

    public function store(array $data): object
    {
        $model = $this->repo->create($data);
        $this->logActivity->log('Tambah Model Pembelajaran', "Model '{$model->nama}' ditambahkan.");

        return $model;
    }

    public function update(int $id, array $data): object
    {
        $model = $this->repo->update($id, $data);
        $this->logActivity->log('Update Model Pembelajaran', "Model '{$model->nama}' diperbarui.");

        return $model;
    }

    public function destroy(int $id): void
    {
        try {
            $model = $this->repo->findById($id);
            $this->repo->delete($id);
            $this->logActivity->log('Hapus Model Pembelajaran', "Model '{$model?->nama}' dihapus.");
        } catch (QueryException) {
            throw new RuntimeException('Model ini sudah dipakai di salah satu RPP. Nonaktifkan saja, jangan dihapus.');
        }
    }

    /**
     * Ganti seluruh daftar sintaks satu model dari baris yang dikirim form
     * (tiap baris: id nullable, nama_sintaks, meta_fase, urutan). Baris yang
     * tidak ikut dikirim dianggap dihapus — tapi ditolak kalau sintaks itu
     * sudah dipakai di RPP yang ada (restrictOnDelete di rpp_inti).
     */
    public function syncSintaks(int $modelId, array $rows): void
    {
        $model = $this->find($modelId);

        DB::transaction(function () use ($model, $rows) {
            $submittedIds = collect($rows)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

            $toDelete = $model->sintaks()->whereNotIn('id', $submittedIds ?: [0])->get();
            foreach ($toDelete as $sintaks) {
                try {
                    $sintaks->delete();
                } catch (QueryException) {
                    throw new RuntimeException("Tahapan sintaks '{$sintaks->nama_sintaks}' sudah dipakai di RPP yang ada, tidak bisa dihapus — nonaktifkan modelnya saja kalau memang sudah tidak dipakai lagi.");
                }
            }

            foreach ($rows as $row) {
                $payload = [
                    'nama_sintaks' => $row['nama_sintaks'],
                    'meta_fase' => $row['meta_fase'],
                    'urutan' => $row['urutan'],
                ];

                if (! empty($row['id'])) {
                    ModelPembelajaranSintaks::where('id', $row['id'])
                        ->where('model_pembelajaran_id', $model->id)
                        ->update($payload);
                } else {
                    $model->sintaks()->create($payload);
                }
            }
        });

        $this->logActivity->log('Kelola Sintaks Model Pembelajaran', "Sintaks model '{$model->nama}' diperbarui.");
    }
}
