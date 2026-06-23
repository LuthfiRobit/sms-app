<?php

namespace App\Services\Master;

use App\Models\Master\Rombel;
use App\Repositories\Master\RombelRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Database\Eloquent\Builder;

class RombelService
{
    public function __construct(
        protected RombelRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    public function datatable(?int $lembagaId): Builder
    {
        $filters = $lembagaId ? ['lembaga_id' => $lembagaId] : [];
        return $this->repo->datatable($filters)
            ->with([
                'lembaga:id,nama,kode',
                'tahunPelajaran:id,nama',
                'jurusan:id,nama,kode',
            ]);
    }

    public function store(array $data): Rombel
    {
        $rombel = $this->repo->create($data);
        $this->logActivity->log('Tambah Rombel', "Rombel '{$rombel->nama}' (Tingkat {$rombel->tingkat}) ditambahkan.");
        return $rombel;
    }

    public function update(int $id, array $data): Rombel
    {
        $rombel = $this->repo->update($id, $data);
        $this->logActivity->log('Update Rombel', "Rombel '{$rombel->nama}' diperbarui.");
        return $rombel;
    }

    public function destroy(int $id): bool
    {
        $rombel = $this->repo->findById($id);
        $name = $rombel?->nama ?? $id;
        $result = $this->repo->delete($id);
        if ($result) {
            $this->logActivity->log('Hapus Rombel', "Rombel '{$name}' dihapus.");
        }
        return $result;
    }
}
