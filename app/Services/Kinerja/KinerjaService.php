<?php

namespace App\Services\Kinerja;

use App\Repositories\Kinerja\KpiRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KinerjaService
{
    public function __construct(
        protected KpiRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    /**
     * Aggregate KPI dashboard data for a given lembaga and tahun pelajaran.
     *
     * Returns:
     *   kpis    - flat Collection of all KpiIndikator (with latestRealisasi loaded)
     *   grouped - Collection grouped by kategori
     *   stats   - [total, hijau (>=90%), kuning (70–89%), merah (<70%), tanpa_data]
     */
    public function getDashboardData(int $lembagaId, int $tahunId): array
    {
        $kpis = $this->repo->getByLembagaTahun($lembagaId, $tahunId);

        // Enrich each KPI with computed pencapaian and trafficLight
        $maxUpdatedAt = null;
        $kpis = $kpis->map(function ($kpi) use (&$maxUpdatedAt) {
            $realisasi = $kpi->latestRealisasi;

            if ($realisasi && $kpi->target > 0) {
                $pencapaian          = round(($realisasi->nilai_realisasi / $kpi->target) * 100, 2);
                $kpi->pencapaian     = $pencapaian;
                $kpi->traffic_light  = $this->resolveTrafficLight($pencapaian);
            } else {
                $kpi->pencapaian    = null;
                $kpi->traffic_light = 'tanpa_data';
            }

            // Flatten realisasi fields expected by the JS dashboard
            $kpi->pencapaian_persen = $kpi->pencapaian;
            $kpi->nilai_realisasi   = $realisasi?->nilai_realisasi;
            $kpi->periode           = $realisasi?->periode;

            $updatedAt = $realisasi?->updated_at;
            $kpi->last_updated = $updatedAt?->format('d M Y');

            if ($updatedAt && (! $maxUpdatedAt || $updatedAt->isAfter($maxUpdatedAt))) {
                $maxUpdatedAt = $updatedAt;
            }

            return $kpi;
        });

        $kategoriConfig = \App\Models\Kinerja\KpiIndikator::kategoriConfig();
        $grouped = $kpis->groupBy('kategori')->map(function ($items, $key) use ($kategoriConfig) {
            return [
                'kategori' => $key,
                'label'    => $kategoriConfig[$key]['label'] ?? ucfirst($key),
                'color'    => $kategoriConfig[$key]['color'] ?? 'secondary',
                'icon'     => $kategoriConfig[$key]['icon'] ?? 'bi-grid',
                'items'    => $items->values(),
            ];
        })->values();

        $stats = [
            'total'      => $kpis->count(),
            'hijau'      => $kpis->filter(fn ($k) => $k->traffic_light === 'hijau')->count(),
            'kuning'     => $kpis->filter(fn ($k) => $k->traffic_light === 'kuning')->count(),
            'merah'      => $kpis->filter(fn ($k) => $k->traffic_light === 'merah')->count(),
            'tanpa_data' => $kpis->filter(fn ($k) => $k->traffic_light === 'tanpa_data')->count(),
        ];

        $last_updated = $maxUpdatedAt?->format('d M Y H:i');

        return compact('kpis', 'grouped', 'stats', 'last_updated');
    }

    /**
     * Create a new KPI indikator and log the activity.
     */
    public function createIndikator(array $data): object
    {
        $ind = $this->repo->createIndikator($data);

        $this->logActivity->log('Buat KPI', "Indikator: {$ind->nama_indikator}");

        return $ind;
    }

    /**
     * Update an existing KPI indikator and log the activity.
     */
    public function updateIndikator(int $id, array $data): object
    {
        $ind = $this->repo->updateIndikator($id, $data);

        $this->logActivity->log('Update KPI', "Indikator: {$ind->nama_indikator}");

        return $ind;
    }

    /**
     * Delete a KPI indikator.
     * Throws \Exception when the indikator still has realisasi records.
     */
    public function deleteIndikator(int $id): void
    {
        $ind = $this->repo->findById($id);

        if ($ind && $ind->realisasi()->count() > 0) {
            throw new \Exception('Indikator memiliki data realisasi, tidak dapat dihapus.');
        }

        $this->repo->deleteIndikator($id);

        $this->logActivity->log('Hapus KPI', "Indikator ID: {$id}" . ($ind ? " ({$ind->nama_indikator})" : ''));
    }

    /**
     * Record or overwrite a realisasi value for an indikator in a given periode.
     */
    public function inputRealisasi(int $indikatorId, string $periode, float $nilai, ?string $catatan, int $userId): object
    {
        $r = $this->repo->upsertRealisasi($indikatorId, $periode, $nilai, $catatan, $userId);

        $this->logActivity->log('Input Realisasi KPI', "Indikator ID: {$indikatorId}, Nilai: {$nilai}");

        return $r;
    }

    /**
     * Auto-calculate and persist realisasi for all is_auto KPIs
     * belonging to a lembaga and tahun pelajaran.
     *
     * Supported sumber_data values:
     *   akademik_nilai   – average nilai_akhir from nilai table
     *   ppdb_pendaftar   – total registrant count (current year)
     *   ppdb_diterima    – acceptance rate (%)
     *   program_kerja    – program-kerja completion rate (%)
     *   absensi          – attendance (hadir) rate (%)
     *   manual           – skipped (is_auto = false)
     *
     * Returns ['synced' => int, 'results' => array]
     */
    public function syncAutoKpi(int $lembagaId, int $tahunId, string $periode, int $userId): array
    {
        $autoKpis = $this->repo
            ->getByLembagaTahun($lembagaId, $tahunId)
            ->filter(fn ($k) => (bool) $k->is_auto);

        $results = [];
        $count   = 0;

        foreach ($autoKpis as $kpi) {
            $nilai = match ($kpi->sumber_data) {
                'akademik_nilai' => $this->calcAkademikNilai($lembagaId, $tahunId),
                'ppdb_pendaftar' => $this->calcPpdbPendaftar($lembagaId),
                'ppdb_diterima'  => $this->calcPpdbDiterima($lembagaId),
                'program_kerja'  => $this->calcProgramKerja($lembagaId, $tahunId),
                'absensi'        => $this->calcAbsensi($lembagaId),
                default          => null,
            };

            if ($nilai === null) {
                // sumber_data 'manual' or unrecognised — skip
                continue;
            }

            $this->repo->upsertRealisasi($kpi->id, $periode, $nilai, null, $userId);

            $results[] = [
                'id'          => $kpi->id,
                'nama'        => $kpi->nama_indikator,
                'sumber_data' => $kpi->sumber_data,
                'nilai'       => $nilai,
            ];

            $count++;
        }

        $this->logActivity->log('Sync Auto KPI', "Lembaga {$lembagaId}, {$count} indikator diperbarui");

        return ['synced' => $count, 'results' => $results];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function resolveTrafficLight(float $pencapaian): string
    {
        if ($pencapaian >= 90) {
            return 'hijau';
        }

        if ($pencapaian >= 70) {
            return 'kuning';
        }

        return 'merah';
    }

    private function calcAkademikNilai(int $lembagaId, int $tahunId): float
    {
        $avg = DB::table('nilai')
            ->where('lembaga_id', $lembagaId)
            ->where('tahun_pelajaran_id', $tahunId)
            ->whereNotNull('nilai_akhir')
            ->avg('nilai_akhir');

        return round($avg ?? 0, 2);
    }

    private function calcPpdbPendaftar(int $lembagaId): float
    {
        $count = DB::table('pendaftaran')
            ->join('pembukaan_ppdb', 'pembukaan_ppdb.id', '=', 'pendaftaran.pembukaan_id')
            ->where('pembukaan_ppdb.lembaga_id', $lembagaId)
            ->whereYear('pendaftaran.created_at', Carbon::now()->year)
            ->count();

        return (float) $count;
    }

    private function calcPpdbDiterima(int $lembagaId): float
    {
        $total = DB::table('pendaftaran')
            ->join('pembukaan_ppdb', 'pembukaan_ppdb.id', '=', 'pendaftaran.pembukaan_id')
            ->where('pembukaan_ppdb.lembaga_id', $lembagaId)
            ->count();

        $diterima = DB::table('pendaftaran')
            ->join('pembukaan_ppdb', 'pembukaan_ppdb.id', '=', 'pendaftaran.pembukaan_id')
            ->where('pembukaan_ppdb.lembaga_id', $lembagaId)
            ->where('pendaftaran.status', 'lulus')
            ->count();

        return $total > 0 ? round(($diterima / $total) * 100, 2) : 0;
    }

    private function calcProgramKerja(int $lembagaId, int $tahunId): float
    {
        $totalK = DB::table('kegiatan_program_kerja')
            ->join('program_kerja', 'program_kerja.id', '=', 'kegiatan_program_kerja.program_kerja_id')
            ->where('program_kerja.lembaga_id', $lembagaId)
            ->where('program_kerja.tahun_pelajaran_id', $tahunId)
            ->count();

        $selesai = DB::table('kegiatan_program_kerja')
            ->join('program_kerja', 'program_kerja.id', '=', 'kegiatan_program_kerja.program_kerja_id')
            ->where('program_kerja.lembaga_id', $lembagaId)
            ->where('program_kerja.tahun_pelajaran_id', $tahunId)
            ->where('kegiatan_program_kerja.status_kegiatan', 'selesai')
            ->count();

        return $totalK > 0 ? round(($selesai / $totalK) * 100, 2) : 0;
    }

    private function calcAbsensi(int $lembagaId): float
    {
        $totalDetail = DB::table('absensi_detail')
            ->join('absensi', 'absensi.id', '=', 'absensi_detail.absensi_id')
            ->where('absensi.lembaga_id', $lembagaId)
            ->count();

        $hadir = DB::table('absensi_detail')
            ->join('absensi', 'absensi.id', '=', 'absensi_detail.absensi_id')
            ->where('absensi.lembaga_id', $lembagaId)
            ->where('absensi_detail.status', 'hadir')
            ->count();

        return $totalDetail > 0 ? round(($hadir / $totalDetail) * 100, 2) : 0;
    }
}
