<?php

namespace App\Services\Master;

use App\Models\Master\Jurusan;
use App\Repositories\Master\JurusanRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Database\Eloquent\Builder;

class JurusanService
{
    public function __construct(
        protected JurusanRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    public function datatable(?int $lembagaId): Builder
    {
        $filters = $lembagaId ? ['lembaga_id' => $lembagaId] : [];
        return $this->repo->datatable($filters)->with('lembaga:id,nama,kode');
    }

    public function store(array $data): Jurusan
    {
        $jurusan = $this->repo->create($data);
        $this->logActivity->log('Tambah Jurusan', "Jurusan '{$jurusan->nama}' ({$jurusan->kode}) ditambahkan.");
        return $jurusan;
    }

    public function update(int $id, array $data): Jurusan
    {
        $jurusan = $this->repo->update($id, $data);
        $this->logActivity->log('Update Jurusan', "Jurusan '{$jurusan->nama}' diperbarui.");
        return $jurusan;
    }

    public function destroy(int $id): bool
    {
        $jurusan = $this->repo->findById($id);
        if ($jurusan && $jurusan->kuotaJurusan()->exists()) {
            throw new \Exception('Jurusan tidak dapat dihapus karena sudah digunakan pada kuota PPDB.');
        }
        $name = $jurusan?->nama ?? $id;
        $result = $this->repo->delete($id);
        if ($result) {
            $this->logActivity->log('Hapus Jurusan', "Jurusan '{$name}' dihapus.");
        }
        return $result;
    }
}
