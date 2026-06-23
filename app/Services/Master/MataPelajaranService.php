<?php

namespace App\Services\Master;

use App\Models\Master\MataPelajaran;
use App\Repositories\Master\MataPelajaranRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Database\Eloquent\Builder;

class MataPelajaranService
{
    public function __construct(
        protected MataPelajaranRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    public function datatable(?int $lembagaId): Builder
    {
        return $this->repo->datatable()
            ->when($lembagaId, fn ($q) => $q->where(
                fn ($q2) => $q2->where('lembaga_id', $lembagaId)->orWhereNull('lembaga_id')
            ))
            ->with('lembaga:id,nama,kode');
    }

    public function store(array $data): MataPelajaran
    {
        $mapel = $this->repo->create($data);
        $this->logActivity->log('Tambah Mata Pelajaran', "Mata pelajaran '{$mapel->nama}' ({$mapel->kode}) ditambahkan.");
        return $mapel;
    }

    public function update(int $id, array $data): MataPelajaran
    {
        $mapel = $this->repo->update($id, $data);
        $this->logActivity->log('Update Mata Pelajaran', "Mata pelajaran '{$mapel->nama}' diperbarui.");
        return $mapel;
    }

    public function destroy(int $id): bool
    {
        $mapel = $this->repo->findById($id, ['jurusan']);
        if ($mapel && $mapel->jurusan->isNotEmpty()) {
            throw new \Exception('Mata pelajaran tidak dapat dihapus karena sudah terhubung ke jurusan.');
        }
        $name = $mapel?->nama ?? $id;
        $result = $this->repo->delete($id);
        if ($result) {
            $this->logActivity->log('Hapus Mata Pelajaran', "Mata pelajaran '{$name}' dihapus.");
        }
        return $result;
    }
}
