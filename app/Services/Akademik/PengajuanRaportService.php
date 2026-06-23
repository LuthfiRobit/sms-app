<?php

namespace App\Services\Akademik;

use App\Models\Akademik\RaportNilai;
use App\Repositories\Akademik\AkademikSettingRepositoryInterface;
use App\Repositories\Akademik\PengajuanRaportRepositoryInterface;
use App\Services\LogActivityService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PengajuanRaportService
{
    public function __construct(
        protected PengajuanRaportRepositoryInterface $repo,
        protected AkademikSettingRepositoryInterface $settingRepo,
        protected LogActivityService $logActivity,
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    /** Return a plain-object default setting (not persisted). */
    private function defaultSetting(): object
    {
        return (object) [
            'allow_manual_nilai' => false,
            'bobot_harian'       => 40.00,
            'bobot_uts'          => 30.00,
            'bobot_uas'          => 30.00,
            'kkm_default'        => 70,
        ];
    }

    /** Resolve the lembaga setting, falling back to defaults if none exists. */
    private function resolveSetting(int $lembagaId): object
    {
        return $this->settingRepo->findByLembaga($lembagaId) ?? $this->defaultSetting();
    }

    /** Calculate nilai_akhir; returns null if any component is null. */
    private function hitungNilaiAkhir(?float $h, ?float $u, ?float $a, object $setting): ?float
    {
        if ($h === null || $u === null || $a === null) {
            return null;
        }

        return round(
            ($h * ($setting->bobot_harian / 100))
            + ($u * ($setting->bobot_uts    / 100))
            + ($a * ($setting->bobot_uas    / 100)),
            2
        );
    }

    /** Build raport_nilai rows as a siswa × mapel cross-product. */
    private function buildNilaiRows(
        int $pengajuanId,
        Collection $siswa,
        Collection $mapel,
        Collection $existingNilai,
        object $setting
    ): array {
        $rows = [];

        foreach ($siswa as $s) {
            foreach ($mapel as $m) {
                $key = $s->peserta_id . '_' . $m->id;
                $n   = $existingNilai->get($key);

                $h = isset($n->nilai_harian) ? (float) $n->nilai_harian : null;
                $u = isset($n->nilai_uts)    ? (float) $n->nilai_uts    : null;
                $a = isset($n->nilai_uas)    ? (float) $n->nilai_uas    : null;

                $nilaiAkhir = $this->hitungNilaiAkhir($h, $u, $a, $setting);
                $predikat   = $nilaiAkhir !== null ? RaportNilai::hitungPredikat($nilaiAkhir) : null;

                $rows[] = [
                    'pengajuan_raport_id' => $pengajuanId,
                    'peserta_id'          => $s->peserta_id,
                    'mata_pelajaran_id'   => $m->id,
                    'nilai_harian'        => $h,
                    'nilai_uts'           => $u,
                    'nilai_uas'           => $a,
                    'nilai_akhir'         => $nilaiAkhir,
                    'predikat'            => $predikat,
                    'catatan_guru'        => $n->catatan ?? null,
                ];
            }
        }

        return $rows;
    }

    /** Build absensi_rekap rows per-siswa from absensi_detail pivot. */
    private function buildAbsensiRows(int $pengajuanId, int $rombelId, Collection $siswa): array
    {
        $rawAbsensi = DB::table('absensi_detail')
            ->join('absensi', 'absensi.id', '=', 'absensi_detail.absensi_id')
            ->where('absensi.rombel_id', $rombelId)
            ->select(
                'absensi_detail.peserta_id',
                'absensi_detail.status',
                DB::raw('COUNT(*) as jumlah')
            )
            ->groupBy('absensi_detail.peserta_id', 'absensi_detail.status')
            ->get();

        // Index: peserta_id → [status => count]
        $absensiMap = [];
        foreach ($rawAbsensi as $row) {
            $absensiMap[$row->peserta_id][$row->status] = (int) $row->jumlah;
        }

        $rows = [];
        foreach ($siswa as $s) {
            $map    = $absensiMap[$s->peserta_id] ?? [];
            $rows[] = [
                'pengajuan_raport_id' => $pengajuanId,
                'peserta_id'          => $s->peserta_id,
                'hadir'               => $map['hadir'] ?? 0,
                'sakit'               => $map['sakit'] ?? 0,
                'izin'                => $map['izin']  ?? 0,
                'alpa'                => $map['alpa']  ?? 0,
            ];
        }

        return $rows;
    }

    /** Fetch active siswa for a rombel, ordered by no_absen then nama. */
    private function fetchSiswa(int $rombelId): Collection
    {
        return DB::table('rombel_siswa')
            ->join('peserta', 'peserta.id', '=', 'rombel_siswa.peserta_id')
            ->where('rombel_siswa.rombel_id', $rombelId)
            ->whereNull('peserta.deleted_at')
            ->select('peserta.id as peserta_id', 'peserta.nama_lengkap', 'rombel_siswa.no_absen')
            ->orderBy('rombel_siswa.no_absen')
            ->orderBy('peserta.nama_lengkap')
            ->get();
    }

    /** Fetch active mata pelajaran for a lembaga, ordered by urutan then nama. */
    private function fetchMapel(int $lembagaId): Collection
    {
        return DB::table('mata_pelajaran')
            ->where('lembaga_id', $lembagaId)
            ->where('status', 'aktif')
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode', 'urutan']);
    }

    /** Fetch existing nilai keyed by "peserta_id_mapel_id". */
    private function fetchExistingNilai(int $rombelId, int $semesterId, int $tahunId): Collection
    {
        return DB::table('nilai')
            ->where('rombel_id', $rombelId)
            ->where('semester_id', $semesterId)
            ->where('tahun_pelajaran_id', $tahunId)
            ->get()
            ->keyBy(fn ($n) => $n->peserta_id . '_' . $n->mata_pelajaran_id);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Create a new draft pengajuan raport, populating raport_nilai and
     * raport_absensi_rekap from the source tables.
     *
     * Required $data keys: lembaga_id, rombel_id, semester_id, tahun_pelajaran_id, dibuat_oleh
     */
    public function create(array $data): object
    {
        $lembagaId  = (int) $data['lembaga_id'];
        $rombelId   = (int) $data['rombel_id'];
        $semesterId = (int) $data['semester_id'];
        $tahunId    = (int) $data['tahun_pelajaran_id'];
        $dibuatOleh = (int) $data['dibuat_oleh'];

        // 1. Guard: no non-rejected pengajuan already exists for the same triplet
        $existing = DB::table('pengajuan_raport')
            ->where('rombel_id', $rombelId)
            ->where('semester_id', $semesterId)
            ->where('tahun_pelajaran_id', $tahunId)
            ->where('status', '!=', 'ditolak')
            ->first();

        if ($existing) {
            throw new RuntimeException(
                'Pengajuan raport untuk rombel, semester, dan tahun pelajaran ini sudah ada (status: ' . $existing->status . ').'
            );
        }

        // 2. Setting
        $setting = $this->resolveSetting($lembagaId);

        // 3. Siswa in rombel
        $siswa = $this->fetchSiswa($rombelId);

        // 4. Active mata pelajaran
        $mapel = $this->fetchMapel($lembagaId);

        // 5. Existing nilai from source table (keyed peserta_mapel)
        $existingNilai = $this->fetchExistingNilai($rombelId, $semesterId, $tahunId);

        // 6. Persist the pengajuan header
        $pengajuan = $this->repo->create([
            'lembaga_id'         => $lembagaId,
            'rombel_id'          => $rombelId,
            'semester_id'        => $semesterId,
            'tahun_pelajaran_id' => $tahunId,
            'dibuat_oleh'        => $dibuatOleh,
            'status'             => 'draft',
        ]);

        // 7. Build and upsert nilai rows (siswa × mapel)
        $nilaiRows = $this->buildNilaiRows($pengajuan->id, $siswa, $mapel, $existingNilai, $setting);
        if (! empty($nilaiRows)) {
            $this->repo->upsertNilai($nilaiRows);
        }

        // 8-9. Build and upsert absensi rekap rows
        $absensiRows = $this->buildAbsensiRows($pengajuan->id, $rombelId, $siswa);
        if (! empty($absensiRows)) {
            $this->repo->upsertAbsensiRekap($absensiRows);
        }

        // 10. Audit log
        $this->logActivity->log(
            'Buat Pengajuan Raport',
            "Pengajuan raport id={$pengajuan->id} dibuat. Rombel={$rombelId}, Semester={$semesterId}, TahunPelajaran={$tahunId}. Siswa=" . count($siswa) . ', Mapel=' . count($mapel) . '.'
        );

        return $pengajuan;
    }

    /**
     * Submit a draft (or previously rejected) pengajuan for review.
     */
    public function submit(int $id, ?string $catatan = null): object
    {
        $pengajuan = $this->repo->findById($id, []);

        if (! $pengajuan) {
            throw new RuntimeException("Pengajuan raport id={$id} tidak ditemukan.");
        }

        if (! in_array($pengajuan->status, ['draft', 'ditolak'], true)) {
            throw new RuntimeException(
                "Hanya pengajuan berstatus draft atau ditolak yang dapat diajukan. Status saat ini: {$pengajuan->status}."
            );
        }

        $pengajuan = $this->repo->update($id, [
            'status'            => 'diajukan',
            'diajukan_at'       => now(),
            'catatan_pengajuan' => $catatan,
        ]);

        $this->logActivity->log('Submit Pengajuan Raport', "Pengajuan raport id={$id} diajukan.");

        return $pengajuan;
    }

    /**
     * Withdraw a submitted pengajuan back to draft.
     */
    public function withdraw(int $id): object
    {
        $pengajuan = $this->repo->findById($id, []);

        if (! $pengajuan) {
            throw new RuntimeException("Pengajuan raport id={$id} tidak ditemukan.");
        }

        if ($pengajuan->status !== 'diajukan') {
            throw new RuntimeException(
                "Hanya pengajuan berstatus diajukan yang dapat ditarik kembali. Status saat ini: {$pengajuan->status}."
            );
        }

        $pengajuan = $this->repo->update($id, [
            'status'      => 'draft',
            'diajukan_at' => null,
        ]);

        $this->logActivity->log('Tarik Pengajuan Raport', "Pengajuan raport id={$id} ditarik kembali ke draft.");

        return $pengajuan;
    }

    /**
     * Re-sync nilai and absensi data from source tables for a draft pengajuan.
     */
    public function refreshNilai(int $id): object
    {
        $pengajuan = $this->repo->findById($id, []);

        if (! $pengajuan) {
            throw new RuntimeException("Pengajuan raport id={$id} tidak ditemukan.");
        }

        if ($pengajuan->status !== 'draft') {
            throw new RuntimeException(
                "Nilai hanya dapat di-refresh pada pengajuan berstatus draft. Status saat ini: {$pengajuan->status}."
            );
        }

        $lembagaId  = $pengajuan->lembaga_id;
        $rombelId   = $pengajuan->rombel_id;
        $semesterId = $pengajuan->semester_id;
        $tahunId    = $pengajuan->tahun_pelajaran_id;

        $setting       = $this->resolveSetting($lembagaId);
        $siswa         = $this->fetchSiswa($rombelId);
        $mapel         = $this->fetchMapel($lembagaId);
        $existingNilai = $this->fetchExistingNilai($rombelId, $semesterId, $tahunId);

        $nilaiRows = $this->buildNilaiRows($pengajuan->id, $siswa, $mapel, $existingNilai, $setting);
        if (! empty($nilaiRows)) {
            $this->repo->upsertNilai($nilaiRows);
        }

        $absensiRows = $this->buildAbsensiRows($pengajuan->id, $rombelId, $siswa);
        if (! empty($absensiRows)) {
            $this->repo->upsertAbsensiRekap($absensiRows);
        }

        $this->logActivity->log(
            'Refresh Nilai Pengajuan Raport',
            "Nilai pengajuan raport id={$id} di-refresh dari data sumber."
        );

        return $pengajuan;
    }

    /**
     * Manually update nilai for a single siswa on a draft pengajuan.
     * Requires allow_manual_nilai = true in the lembaga's akademik setting.
     *
     * Each $nilaiRows entry: [mata_pelajaran_id, nilai_harian, nilai_uts, nilai_uas, nilai_akhir_manual?, catatan_guru?]
     */
    public function updateNilaiSiswa(int $pengajuanId, int $pesertaId, array $nilaiRows): void
    {
        $pengajuan = $this->repo->findById($pengajuanId, []);

        if (! $pengajuan) {
            throw new RuntimeException("Pengajuan raport id={$pengajuanId} tidak ditemukan.");
        }

        if ($pengajuan->status !== 'draft') {
            throw new RuntimeException(
                "Nilai siswa hanya dapat diubah pada pengajuan berstatus draft. Status saat ini: {$pengajuan->status}."
            );
        }

        $setting = $this->resolveSetting($pengajuan->lembaga_id);

        if (! $setting->allow_manual_nilai) {
            throw new RuntimeException(
                'Input nilai manual tidak diizinkan. Aktifkan pengaturan allow_manual_nilai terlebih dahulu.'
            );
        }

        $upsertRows = [];
        foreach ($nilaiRows as $row) {
            $h = isset($row['nilai_harian']) && $row['nilai_harian'] !== '' ? (float) $row['nilai_harian'] : null;
            $u = isset($row['nilai_uts'])    && $row['nilai_uts']    !== '' ? (float) $row['nilai_uts']    : null;
            $a = isset($row['nilai_uas'])    && $row['nilai_uas']    !== '' ? (float) $row['nilai_uas']    : null;

            // Use nilai_akhir_manual if explicitly provided, otherwise calculate
            if (isset($row['nilai_akhir_manual']) && $row['nilai_akhir_manual'] !== '') {
                $nilaiAkhir = round((float) $row['nilai_akhir_manual'], 2);
            } else {
                $nilaiAkhir = $this->hitungNilaiAkhir($h, $u, $a, $setting);
            }

            $predikat = $nilaiAkhir !== null ? RaportNilai::hitungPredikat($nilaiAkhir) : null;

            $upsertRows[] = [
                'pengajuan_raport_id' => $pengajuanId,
                'peserta_id'          => $pesertaId,
                'mata_pelajaran_id'   => (int) $row['mata_pelajaran_id'],
                'nilai_harian'        => $h,
                'nilai_uts'           => $u,
                'nilai_uas'           => $a,
                'nilai_akhir'         => $nilaiAkhir,
                'predikat'            => $predikat,
                'catatan_guru'        => $row['catatan_guru'] ?? null,
            ];
        }

        if (! empty($upsertRows)) {
            $this->repo->upsertNilai($upsertRows);
        }

        $this->logActivity->log(
            'Update Nilai Siswa',
            "Nilai manual siswa peserta_id={$pesertaId} pada pengajuan_raport_id={$pengajuanId} diperbarui (" . count($upsertRows) . ' mapel).'
        );
    }

    /**
     * Mark a submitted pengajuan as verified (diverifikasi).
     */
    public function verifikasi(int $id, ?string $catatan, int $verifikasiBy): object
    {
        $pengajuan = $this->repo->findById($id, []);

        if (! $pengajuan) {
            throw new RuntimeException("Pengajuan raport id={$id} tidak ditemukan.");
        }

        if ($pengajuan->status !== 'diajukan') {
            throw new RuntimeException(
                "Hanya pengajuan berstatus diajukan yang dapat diverifikasi. Status saat ini: {$pengajuan->status}."
            );
        }

        $pengajuan = $this->repo->update($id, [
            'status'             => 'diverifikasi',
            'diverifikasi_at'    => now(),
            'diverifikasi_by'    => $verifikasiBy,
            'catatan_verifikasi' => $catatan,
        ]);

        $this->logActivity->log(
            'Verifikasi Pengajuan Raport',
            "Pengajuan raport id={$id} diverifikasi oleh user_id={$verifikasiBy}."
        );

        return $pengajuan;
    }

    /**
     * Approve a verified pengajuan (disetujui).
     */
    public function approval(int $id, ?string $catatan, int $approvalBy): object
    {
        $pengajuan = $this->repo->findById($id, []);

        if (! $pengajuan) {
            throw new RuntimeException("Pengajuan raport id={$id} tidak ditemukan.");
        }

        if ($pengajuan->status !== 'diverifikasi') {
            throw new RuntimeException(
                "Hanya pengajuan berstatus diverifikasi yang dapat disetujui. Status saat ini: {$pengajuan->status}."
            );
        }

        $pengajuan = $this->repo->update($id, [
            'status'           => 'disetujui',
            'disetujui_at'     => now(),
            'disetujui_by'     => $approvalBy,
            'catatan_approval' => $catatan,
        ]);

        $this->logActivity->log(
            'Approval Pengajuan Raport',
            "Pengajuan raport id={$id} disetujui oleh user_id={$approvalBy}."
        );

        return $pengajuan;
    }

    /**
     * Reject a submitted or verified pengajuan.
     * Catatan written to catatan_verifikasi (if diajukan) or catatan_approval (if diverifikasi).
     */
    public function tolak(int $id, string $catatan): object
    {
        $pengajuan = $this->repo->findById($id, []);

        if (! $pengajuan) {
            throw new RuntimeException("Pengajuan raport id={$id} tidak ditemukan.");
        }

        $prevStatus = $pengajuan->status;

        if (! in_array($prevStatus, ['diajukan', 'diverifikasi'], true)) {
            throw new RuntimeException(
                "Hanya pengajuan berstatus diajukan atau diverifikasi yang dapat ditolak. Status saat ini: {$prevStatus}."
            );
        }

        $updateData = ['status' => 'ditolak'];

        if ($prevStatus === 'diajukan') {
            $updateData['catatan_verifikasi'] = $catatan;
        } else {
            $updateData['catatan_approval'] = $catatan;
        }

        $pengajuan = $this->repo->update($id, $updateData);

        $this->logActivity->log(
            'Tolak Pengajuan Raport',
            "Pengajuan raport id={$id} ditolak dari status={$prevStatus}. Catatan: {$catatan}"
        );

        return $pengajuan;
    }

    /**
     * Return all nilai for a pengajuan, grouped by peserta.
     * Returns a Collection of objects: {peserta_id, nama, rows: [...]}
     */
    public function getNilaiSiswa(int $pengajuanId): Collection
    {
        $nilaiCollection = $this->repo->getNilaiByPengajuan($pengajuanId);

        return $nilaiCollection
            ->groupBy('peserta_id')
            ->map(function (Collection $group) {
                $first = $group->first();

                return (object) [
                    'peserta_id' => $first->peserta_id,
                    'nama'       => optional($first->peserta)->nama_lengkap,
                    'rows'       => $group->map(fn ($n) => (object) [
                        'mata_pelajaran_id' => $n->mata_pelajaran_id,
                        'mapel'             => $n->mataPelajaran,
                        'nilai_harian'      => $n->nilai_harian,
                        'nilai_uts'         => $n->nilai_uts,
                        'nilai_uas'         => $n->nilai_uas,
                        'nilai_akhir'       => $n->nilai_akhir,
                        'predikat'          => $n->predikat,
                        'catatan_guru'      => $n->catatan_guru,
                    ])->values(),
                ];
            })
            ->values();
    }

    /**
     * Return distinct siswa list for a pengajuan with their absensi rekap merged.
     * Each entry: {peserta_id, nama, hadir, sakit, izin, alpa}
     */
    public function getSiswaList(int $pengajuanId): Collection
    {
        $absensiRekap = $this->repo->getAbsensiRekap($pengajuanId)
            ->keyBy('peserta_id');

        // Distinct peserta from raport_nilai for this pengajuan
        $siswa = DB::table('raport_nilai')
            ->join('peserta', 'peserta.id', '=', 'raport_nilai.peserta_id')
            ->where('raport_nilai.pengajuan_raport_id', $pengajuanId)
            ->whereNull('peserta.deleted_at')
            ->select('peserta.id as peserta_id', 'peserta.nama_lengkap')
            ->distinct()
            ->orderBy('peserta.nama_lengkap')
            ->get();

        return $siswa->map(function ($s) use ($absensiRekap) {
            $rekap = $absensiRekap->get($s->peserta_id);

            return (object) [
                'peserta_id' => $s->peserta_id,
                'nama'       => $s->nama_lengkap,
                'hadir'      => $rekap?->hadir ?? 0,
                'sakit'      => $rekap?->sakit ?? 0,
                'izin'       => $rekap?->izin  ?? 0,
                'alpa'       => $rekap?->alpa  ?? 0,
            ];
        });
    }
}
