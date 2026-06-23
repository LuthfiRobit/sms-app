<?php

namespace App\Services\Master;

use App\Models\Master\JadwalKbm;
use App\Repositories\Master\JadwalKbmRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Database\Eloquent\Builder;

class JadwalKbmService
{
    public function __construct(
        protected JadwalKbmRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    public function datatable(int $lembagaId, ?int $rombelId = null, ?int $tahunId = null): Builder
    {
        $filters = ['lembaga_id' => $lembagaId];

        if ($rombelId) {
            $filters['rombel_id'] = $rombelId;
        }

        if ($tahunId) {
            $filters['tahun_pelajaran_id'] = $tahunId;
        }

        return $this->repo->datatable($filters);
    }

    public function store(array $data): JadwalKbm
    {
        $jadwal = $this->repo->create($data);
        $this->logActivity->log(
            'Tambah Jadwal KBM',
            "Jadwal KBM hari {$jadwal->hari} jam {$jadwal->jam_mulai}-{$jadwal->jam_selesai} ditambahkan."
        );
        return $jadwal;
    }

    public function update(int $id, array $data): JadwalKbm
    {
        $jadwal = $this->repo->update($id, $data);
        $this->logActivity->log(
            'Update Jadwal KBM',
            "Jadwal KBM ID {$id} hari {$jadwal->hari} diperbarui."
        );
        return $jadwal;
    }

    public function destroy(int $id): void
    {
        $jadwal = $this->repo->findById($id);
        $this->repo->delete($id);
        $this->logActivity->log(
            'Hapus Jadwal KBM',
            "Jadwal KBM ID {$id} dihapus."
        );
    }
}
