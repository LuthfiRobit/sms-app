<?php

namespace App\Services\Akademik;

use App\Repositories\Akademik\NilaiRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class NilaiService
{
    public function __construct(
        protected NilaiRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    /**
     * Return merged list: semua siswa di rombel + nilai yang sudah ada (atau null).
     */
    public function getNilaiSheet(int $rombelId, int $mapelId, int $semesterId): Collection
    {
        // Semua siswa di rombel
        $siswa = DB::table('rombel_siswa')
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

        // Nilai yang sudah ada
        $existing = $this->repo->getByRombelMapelSemester($rombelId, $mapelId, $semesterId)
            ->keyBy('peserta_id');

        return $siswa->map(function ($s) use ($existing) {
            $n = $existing->get($s->peserta_id);
            return (object) [
                'peserta_id'   => $s->peserta_id,
                'nama'         => $s->nama,
                'no_absen'     => $s->no_absen,
                'nilai_harian' => $n?->nilai_harian,
                'nilai_uts'    => $n?->nilai_uts,
                'nilai_uas'    => $n?->nilai_uas,
                'nilai_akhir'  => $n?->nilai_akhir,
                'kkm'          => $n?->kkm ?? 70,
                'catatan'      => $n?->catatan,
            ];
        });
    }

    /**
     * Hitung nilai_akhir lalu upsert semua baris.
     */
    public function save(
        array $rows,
        int $lembagaId,
        int $rombelId,
        int $mapelId,
        int $semesterId,
        int $tahunId
    ): void {
        $upsertRows = [];

        foreach ($rows as $row) {
            $h = isset($row['nilai_harian']) && $row['nilai_harian'] !== '' ? (float) $row['nilai_harian'] : null;
            $u = isset($row['nilai_uts'])    && $row['nilai_uts']    !== '' ? (float) $row['nilai_uts']    : null;
            $a = isset($row['nilai_uas'])    && $row['nilai_uas']    !== '' ? (float) $row['nilai_uas']    : null;

            $nilaiAkhir = null;
            if ($h !== null && $u !== null && $a !== null) {
                $nilaiAkhir = round(($h * 0.4) + ($u * 0.3) + ($a * 0.3), 2);
            }

            $upsertRows[] = [
                'lembaga_id'        => $lembagaId,
                'rombel_id'         => $rombelId,
                'peserta_id'        => (int) $row['peserta_id'],
                'mata_pelajaran_id' => $mapelId,
                'semester_id'       => $semesterId,
                'tahun_pelajaran_id'=> $tahunId,
                'nilai_harian'      => $h,
                'nilai_uts'         => $u,
                'nilai_uas'         => $a,
                'nilai_akhir'       => $nilaiAkhir,
                'catatan'           => $row['catatan'] ?? null,
            ];
        }

        $this->repo->upsert($upsertRows);

        $this->logActivity->log(
            'Input Nilai',
            "Nilai rombel_id={$rombelId} mapel_id={$mapelId} semester_id={$semesterId} disimpan (" . count($upsertRows) . " siswa)."
        );
    }
}
