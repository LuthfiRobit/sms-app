<?php

namespace App\Services\Akademik;

use App\Repositories\Akademik\AkademikSettingRepositoryInterface;
use App\Repositories\Akademik\NilaiRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class NilaiService
{
    public function __construct(
        protected NilaiRepositoryInterface $repo,
        protected AkademikSettingRepositoryInterface $settingRepo,
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
        $setting    = $this->settingRepo->findByLembaga($lembagaId);
        $bobotH     = ($setting?->bobot_harian ?? 40) / 100;
        $bobotU     = ($setting?->bobot_uts    ?? 30) / 100;
        $bobotA     = ($setting?->bobot_uas    ?? 30) / 100;

        $upsertRows = [];

        foreach ($rows as $row) {
            $h = isset($row['nilai_harian']) && $row['nilai_harian'] !== '' ? (float) $row['nilai_harian'] : null;
            $u = isset($row['nilai_uts'])    && $row['nilai_uts']    !== '' ? (float) $row['nilai_uts']    : null;
            $a = isset($row['nilai_uas'])    && $row['nilai_uas']    !== '' ? (float) $row['nilai_uas']    : null;

            $nilaiAkhir = null;
            if ($h !== null && $u !== null && $a !== null) {
                $nilaiAkhir = round(($h * $bobotH) + ($u * $bobotU) + ($a * $bobotA), 2);
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

    /**
     * Rekap nilai seluruh siswa × semua mata pelajaran dalam satu rombel/semester.
     * Returns ['siswa' => Collection, 'mapel' => Collection]
     * Setiap item siswa: { peserta_id, nama, no_absen, nilai[mapel_id] => {harian,uts,uas,akhir,predikat} }
     */
    public function getRekapNilai(int $rombelId, int $semesterId, int $tahunId): array
    {
        // Semua siswa di rombel
        $siswa = DB::table('rombel_siswa')
            ->join('peserta', 'rombel_siswa.peserta_id', '=', 'peserta.id')
            ->where('rombel_siswa.rombel_id', $rombelId)
            ->whereNull('peserta.deleted_at')
            ->orderBy('rombel_siswa.no_absen')
            ->orderBy('peserta.nama_lengkap')
            ->select('peserta.id as peserta_id', 'peserta.nama_lengkap as nama', 'rombel_siswa.no_absen')
            ->get();

        // Semua nilai dalam rombel/semester ini
        $nilaiAll = DB::table('nilai')
            ->where('rombel_id', $rombelId)
            ->where('semester_id', $semesterId)
            ->where('tahun_pelajaran_id', $tahunId)
            ->select('peserta_id','mata_pelajaran_id','nilai_harian','nilai_uts','nilai_uas','nilai_akhir')
            ->get();

        // Daftar mapel yang muncul di nilai
        $mapelIds = $nilaiAll->pluck('mata_pelajaran_id')->unique();
        $mapelList = DB::table('mata_pelajaran')
            ->whereIn('id', $mapelIds)
            ->orderBy('urutan')->orderBy('nama')
            ->select('id','nama','kode')
            ->get();

        // Index nilai: [peserta_id][mapel_id] => row
        $nilaiIdx = [];
        foreach ($nilaiAll as $n) {
            $nilaiIdx[$n->peserta_id][$n->mata_pelajaran_id] = $n;
        }

        $siswaRekap = $siswa->map(function ($s) use ($nilaiIdx, $mapelList) {
            $nilaiPerMapel = [];
            foreach ($mapelList as $m) {
                $n = $nilaiIdx[$s->peserta_id][$m->id] ?? null;
                $akhir = $n?->nilai_akhir;
                $nilaiPerMapel[$m->id] = (object) [
                    'harian'   => $n?->nilai_harian,
                    'uts'      => $n?->nilai_uts,
                    'uas'      => $n?->nilai_uas,
                    'akhir'    => $akhir,
                    'predikat' => $akhir === null ? '-' : ($akhir >= 90 ? 'A' : ($akhir >= 75 ? 'B' : ($akhir >= 60 ? 'C' : ($akhir >= 40 ? 'D' : 'E')))),
                ];
            }
            return (object) [
                'peserta_id'    => $s->peserta_id,
                'nama'          => $s->nama,
                'no_absen'      => $s->no_absen,
                'nilai'         => $nilaiPerMapel,
                'rata_rata'     => $nilaiAll->where('peserta_id', $s->peserta_id)->avg('nilai_akhir'),
            ];
        });

        return ['siswa' => $siswaRekap, 'mapel' => $mapelList];
    }
}
