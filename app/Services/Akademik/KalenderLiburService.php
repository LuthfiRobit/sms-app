<?php

namespace App\Services\Akademik;

use App\Models\Akademik\KalenderLibur;
use App\Repositories\Akademik\KalenderLiburRepositoryInterface;
use App\Services\LogActivityService;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use RuntimeException;

class KalenderLiburService
{
    /** Batas maksimal rentang tanggal quick-add, mencegah input tahun yang salah ketik. */
    protected const MAX_RENTANG_HARI = 92;

    public function __construct(
        protected KalenderLiburRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    public function datatable(?int $lembagaId, array $filters = []): mixed
    {
        if ($lembagaId) {
            $filters['lembaga_id'] = $lembagaId;
        }

        return $this->repo->datatable($filters);
    }

    public function find(int $id): KalenderLibur
    {
        $row = $this->repo->findById($id);

        if (! $row) {
            throw new RuntimeException('Data kalender libur tidak ditemukan.');
        }

        return $row;
    }

    public function store(array $data): KalenderLibur
    {
        $lembagaId = $data['lembaga_id'] ?? null;

        if ($this->repo->findExisting($data['tanggal'], $lembagaId)) {
            throw new RuntimeException('Tanggal ini sudah terdaftar sebagai hari libur.');
        }

        $row = $this->repo->create($data);
        $this->logActivity->log('Tambah Kalender Libur', "Libur '{$row->keterangan}' pada {$row->tanggal->toDateString()} ditambahkan.");

        return $row;
    }

    /**
     * Quick-add rentang tanggal (mis. libur Lebaran H-7 s/d H+7). Melewati
     * tanggal yang sudah terdaftar pada scope lembaga yang sama, mengembalikan
     * jumlah baris yang benar-benar ditambahkan.
     */
    public function storeRentang(array $data): int
    {
        $mulai = Carbon::parse($data['tanggal_mulai']);
        $akhir = Carbon::parse($data['tanggal_akhir']);
        $lembagaId = $data['lembaga_id'] ?? null;

        if ($mulai->diffInDays($akhir) > self::MAX_RENTANG_HARI) {
            throw new RuntimeException('Rentang tanggal maksimal '.self::MAX_RENTANG_HARI.' hari.');
        }

        $existing = $this->repo->existingDatesInRange($mulai->toDateString(), $akhir->toDateString(), $lembagaId);

        $rows = [];
        foreach (CarbonPeriod::create($mulai, $akhir) as $tanggal) {
            if (in_array($tanggal->toDateString(), $existing, true)) {
                continue;
            }
            $rows[] = [
                'lembaga_id' => $lembagaId,
                'tanggal' => $tanggal->toDateString(),
                'keterangan' => $data['keterangan'],
                'jenis' => $data['jenis'] ?? 'libur',
                'dibuat_oleh' => $data['dibuat_oleh'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $jumlah = $this->repo->createMany($rows);
        $this->logActivity->log('Tambah Rentang Kalender Libur', "{$jumlah} tanggal libur '{$data['keterangan']}' ditambahkan ({$mulai->toDateString()} s/d {$akhir->toDateString()}).");

        return $jumlah;
    }

    public function update(int $id, array $data): KalenderLibur
    {
        $row = $this->find($id);
        $row = $this->repo->update($row, $data);
        $this->logActivity->log('Update Kalender Libur', "Libur ID {$id} diperbarui.");

        return $row;
    }

    public function destroy(int $id): void
    {
        $row = $this->find($id);
        $this->repo->delete($row);
        $this->logActivity->log('Hapus Kalender Libur', "Libur '{$row->keterangan}' pada {$row->tanggal->toDateString()} dihapus.");
    }
}
