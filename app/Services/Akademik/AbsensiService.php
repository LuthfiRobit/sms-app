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

    /**
     * Rekap kehadiran per siswa dalam rentang tanggal.
     * Returns collection of { peserta_id, nama, no_absen, hadir, sakit, izin, alpa, total, persen_hadir }
     */
    public function getRekapAbsensi(int $rombelId, string $tanggalMulai, string $tanggalAkhir): Collection
    {
        // Ambil semua siswa di rombel
        $siswa = DB::table('rombel_siswa')
            ->join('peserta', 'rombel_siswa.peserta_id', '=', 'peserta.id')
            ->where('rombel_siswa.rombel_id', $rombelId)
            ->whereNull('peserta.deleted_at')
            ->orderBy('rombel_siswa.no_absen')
            ->orderBy('peserta.nama_lengkap')
            ->select('peserta.id as peserta_id', 'peserta.nama_lengkap as nama', 'rombel_siswa.no_absen')
            ->get();

        // Agregat absensi_detail per peserta dalam rentang tanggal
        $agg = DB::table('absensi_detail')
            ->join('absensi', 'absensi.id', '=', 'absensi_detail.absensi_id')
            ->where('absensi.rombel_id', $rombelId)
            ->whereBetween('absensi.tanggal', [$tanggalMulai, $tanggalAkhir])
            ->selectRaw('
                absensi_detail.peserta_id,
                SUM(CASE WHEN absensi_detail.status = \'hadir\' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN absensi_detail.status = \'sakit\' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN absensi_detail.status = \'izin\'  THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN absensi_detail.status = \'alpa\'  THEN 1 ELSE 0 END) as alpa,
                COUNT(*) as total
            ')
            ->groupBy('absensi_detail.peserta_id')
            ->get()
            ->keyBy('peserta_id');

        return $siswa->map(function ($s) use ($agg) {
            $a     = $agg->get($s->peserta_id);
            $hadir = (int) ($a->hadir ?? 0);
            $sakit = (int) ($a->sakit ?? 0);
            $izin  = (int) ($a->izin  ?? 0);
            $alpa  = (int) ($a->alpa  ?? 0);
            $total = $hadir + $sakit + $izin + $alpa;
            return (object) [
                'peserta_id'   => $s->peserta_id,
                'nama'         => $s->nama,
                'no_absen'     => $s->no_absen,
                'hadir'        => $hadir,
                'sakit'        => $sakit,
                'izin'         => $izin,
                'alpa'         => $alpa,
                'total'        => $total,
                'persen_hadir' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
            ];
        });
    }
}
