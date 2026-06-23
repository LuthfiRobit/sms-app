<?php

namespace App\Services\Master;

use App\Models\Master\Guru;
use App\Repositories\Master\GuruRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Database\Eloquent\Builder;

class GuruService
{
    public function __construct(
        protected GuruRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    public function datatable(?int $lembagaId): Builder
    {
        $filters = [];
        if ($lembagaId) {
            $filters['lembaga_id'] = $lembagaId;
        }
        return $this->repo->datatable($filters);
    }

    public function store(array $data): Guru
    {
        $guru = $this->repo->create($data);
        $this->logActivity->log('Tambah Guru', "Guru '{$guru->nama_lengkap}' berhasil ditambahkan.");
        return $guru;
    }

    public function update(int $id, array $data): Guru
    {
        $guru = $this->repo->update($id, $data);
        $this->logActivity->log('Update Guru', "Data guru '{$guru->nama_lengkap}' diperbarui.");
        return $guru;
    }

    public function destroy(int $id): void
    {
        $guru = $this->repo->findById($id);
        $name = $guru?->nama_lengkap ?? $id;
        $this->repo->delete($id);
        $this->logActivity->log('Hapus Guru', "Guru '{$name}' dihapus.");
    }
}
