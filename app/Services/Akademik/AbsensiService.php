<?php

namespace App\Services\Akademik;

use App\Repositories\Akademik\AbsensiRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AbsensiService
{
    public function __construct(
        protected AbsensiRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    public function datatable(int $lembagaId, ?int $rombelId = null): mixed
    {
        $filters = ['lembaga_id' => $lembagaId];
        if ($rombelId) {
            $filters['rombel_id'] = $rombelId;
        }
        return $this->repo->datatable($filters);
    }

    public function getSiswaForAbsensi(int $rombelId): Collection
    {
        return DB::table('rombel_siswa')
            ->join('peserta', 'rombel_siswa.peserta_id', '=', 'peserta.id')
            ->where('rombel_siswa.rombel_id', $rombelId)
            ->whereNull('peserta.deleted_at')
            ->orderBy('rombel_siswa.no_absen')
            ->orderBy('peserta.nama_lengkap')
            ->select([
                'peserta.id as peserta_id',
                'peserta.nama_lengkap as nama',
                'rombel_siswa.no_absen',
            ])
            ->get();
    }

    public function store(array $data, array $details): object
    {
        $absensi = $this->repo->create($data);
        $this->repo->saveDetail($absensi->id, $details);
        $this->logActivity->log('Input Absensi', "Absensi rombel ID {$data['rombel_id']} tanggal {$data['tanggal']} ditambahkan.");
        return $absensi;
    }

    public function update(int $id, array $data, array $details): object
    {
        $absensi = $this->repo->findById($id);
        if (!$absensi) {
            throw new \Exception('Data absensi tidak ditemukan.');
        }
        $absensi->update($data);
        $this->repo->saveDetail($id, $details);
        $this->logActivity->log('Update Absensi', "Absensi ID {$id} diperbarui.");
        return $absensi;
    }

    public function destroy(int $id): void
    {
        $this->repo->delete($id);
        $this->logActivity->log('Hapus Absensi', "Absensi ID {$id} dihapus.");
    }
}
