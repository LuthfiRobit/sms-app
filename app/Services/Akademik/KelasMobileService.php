<?php

namespace App\Services\Akademik;

use App\Models\Akademik\MateriBelajar;
use App\Models\Akademik\PerangkatMengajar;
use App\Models\Master\Guru;
use App\Models\Master\JadwalKbm;
use App\Models\Master\Semester;
use App\Repositories\Akademik\AbsensiRepositoryInterface;
use App\Repositories\Akademik\AkademikSettingRepositoryInterface;
use App\Repositories\Akademik\NilaiRepositoryInterface;
use App\Services\LogActivityService;
use App\Support\QrToken;
use App\Support\WaktuSekolah;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Alur "masuk kelas" untuk aplikasi mobile guru: absen siswa → (gate) materi/RPP
 * & input nilai. Nilai baru terbuka setelah sesi absensi untuk jadwal ini dibuat
 * — keputusan produk: kualitas pengajaran diaudit lewat urutan hadir→ajar→nilai.
 */
class KelasMobileService
{
    public function __construct(
        protected AbsensiRepositoryInterface $absensiRepo,
        protected NilaiRepositoryInterface $nilaiRepo,
        protected LogActivityService $logActivity,
    ) {}

    /** Daftar siswa rombel + status kehadiran hari ini (jika sesi absensi sudah dibuat). */
    public function daftarSiswa(JadwalKbm $jadwal): array
    {
        $tanggal = WaktuSekolah::now()->toDateString();
        $siswa = $this->siswaRombel($jadwal->rombel_id);
        $sesi = $this->absensiRepo->findByRombelMapelTanggal($jadwal->rombel_id, $jadwal->mata_pelajaran_id, $tanggal);

        $statusByPeserta = $sesi
            ? collect($sesi->detail)->keyBy('peserta_id')->map(fn ($d) => $d->status)
            : collect();

        return [
            'tanggal' => $tanggal,
            'absensi_id' => $sesi?->id,
            'absensi_dibuat' => (bool) $sesi,
            'siswa' => $siswa->map(fn ($s) => [
                'peserta_id' => $s->peserta_id,
                'nama' => $s->nama,
                'no_absen' => $s->no_absen,
                'status' => $statusByPeserta->get($s->peserta_id, 'belum'),
            ])->all(),
        ];
    }

    /**
     * Submit absensi lengkap (semua siswa sekaligus) dari form mobile.
     * Membuat sesi jika belum ada, atau menimpa sesi hari ini jika sudah ada.
     */
    public function simpanAbsensi(JadwalKbm $jadwal, Guru $guru, array $detail): object
    {
        $tanggal = WaktuSekolah::now()->toDateString();
        $siswaIds = $this->siswaRombel($jadwal->rombel_id)->pluck('peserta_id')->all();

        foreach ($detail as $d) {
            if (! in_array((int) $d['peserta_id'], $siswaIds, true)) {
                throw new RuntimeException('Salah satu siswa tidak terdaftar di kelas ini.');
            }
        }

        $sesi = $this->absensiRepo->findByRombelMapelTanggal($jadwal->rombel_id, $jadwal->mata_pelajaran_id, $tanggal);

        if (! $sesi) {
            $sesi = $this->absensiRepo->create([
                'lembaga_id' => $jadwal->lembaga_id,
                'rombel_id' => $jadwal->rombel_id,
                'guru_id' => $guru->id,
                'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                'tanggal' => $tanggal,
                'jam_ke' => $jadwal->jam_ke,
            ]);
        }

        $this->absensiRepo->saveDetail($sesi->id, $detail);
        $this->logActivity->log('Absensi Siswa (Mobile)', "Absensi rombel_id={$jadwal->rombel_id} mapel_id={$jadwal->mata_pelajaran_id} tanggal={$tanggal} disimpan via mobile.");

        return $sesi;
    }

    /**
     * Scan QR siswa: buat sesi absensi (default semua alpa) jika belum ada,
     * lalu tandai satu siswa hadir. Cocok untuk alur cepat di kelas.
     */
    public function scanQr(JadwalKbm $jadwal, Guru $guru, string $token): array
    {
        $payload = QrToken::verify($token);

        if (! $payload) {
            throw new RuntimeException('Token QR tidak valid atau sudah kedaluwarsa.');
        }

        $tanggal = WaktuSekolah::now()->toDateString();

        if ($payload['tanggal'] !== $tanggal) {
            throw new RuntimeException('QR ini bukan untuk hari ini.');
        }

        $siswa = $this->siswaRombel($jadwal->rombel_id)->firstWhere('peserta_id', $payload['peserta_id']);

        if (! $siswa) {
            throw new RuntimeException('Siswa tidak terdaftar di kelas ini.');
        }

        $sesi = $this->absensiRepo->findByRombelMapelTanggal($jadwal->rombel_id, $jadwal->mata_pelajaran_id, $tanggal);

        if (! $sesi) {
            $sesi = $this->absensiRepo->create([
                'lembaga_id' => $jadwal->lembaga_id,
                'rombel_id' => $jadwal->rombel_id,
                'guru_id' => $guru->id,
                'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                'tanggal' => $tanggal,
                'jam_ke' => $jadwal->jam_ke,
            ]);

            // Default semua siswa 'alpa'; scan berikutnya menimpa jadi 'hadir' satu per satu.
            $default = $this->siswaRombel($jadwal->rombel_id)
                ->map(fn ($s) => ['peserta_id' => $s->peserta_id, 'status' => 'alpa'])
                ->all();
            $this->absensiRepo->saveDetail($sesi->id, $default);
        }

        $existing = DB::table('absensi_detail')
            ->where('absensi_id', $sesi->id)
            ->where('peserta_id', $siswa->peserta_id)
            ->first();

        if ($existing && $existing->status === 'hadir') {
            return ['peserta_id' => $siswa->peserta_id, 'nama' => $siswa->nama, 'status' => 'hadir', 'sudah_tercatat' => true];
        }

        DB::table('absensi_detail')
            ->where('absensi_id', $sesi->id)
            ->where('peserta_id', $siswa->peserta_id)
            ->update(['status' => 'hadir']);

        $this->logActivity->log('Scan QR Kehadiran (Mobile)', "Siswa {$siswa->nama} (#{$siswa->peserta_id}) discan hadir pada absensi #{$sesi->id}.");

        return ['peserta_id' => $siswa->peserta_id, 'nama' => $siswa->nama, 'status' => 'hadir', 'sudah_tercatat' => false];
    }

    /** RPP (perangkat mengajar) + materi ajar disetujui untuk mapel & rombel jadwal ini. */
    public function materi(JadwalKbm $jadwal): array
    {
        $rpp = PerangkatMengajar::where('guru_id', $jadwal->guru_id)
            ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->where('tahun_pelajaran_id', $jadwal->tahun_pelajaran_id)
            ->orderByDesc('id')
            ->get(['id', 'jenis', 'judul', 'deskripsi', 'file_path', 'file_name']);

        $materi = MateriBelajar::where('guru_id', $jadwal->guru_id)
            ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->where('status', 'disetujui')
            ->where(function ($q) use ($jadwal) {
                $q->where('rombel_id', $jadwal->rombel_id)->orWhereNull('rombel_id');
            })
            ->orderByDesc('tanggal')
            ->get(['id', 'judul', 'deskripsi', 'file_path', 'file_name', 'url_eksternal', 'tanggal', 'pertemuan_ke']);

        $tanggal = WaktuSekolah::now()->toDateString();
        $pastMeetings = DB::table('absensi')
            ->where('rombel_id', $jadwal->rombel_id)
            ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->whereDate('tanggal', '<', $tanggal)
            ->count();

        $pertemuanHariIni = $pastMeetings + 1;

        return [
            'rpp' => $rpp,
            'materi' => $materi,
            'pertemuan_hari_ini' => $pertemuanHariIni,
        ];
    }

    /**
     * Lembar nilai untuk jadwal ini. Terkunci (RuntimeException) jika absensi
     * siswa untuk sesi ini belum dibuat.
     */
    public function nilaiSheet(JadwalKbm $jadwal): array
    {
        $this->guardAbsensiSelesai($jadwal);

        $semester = $this->semesterAktif($jadwal->tahun_pelajaran_id);
        $siswa = $this->siswaRombel($jadwal->rombel_id);
        $existing = $this->nilaiRepo->getByRombelMapelSemester($jadwal->rombel_id, $jadwal->mata_pelajaran_id, $semester->id)
            ->keyBy('peserta_id');

        return $siswa->map(fn ($s) => [
            'peserta_id' => $s->peserta_id,
            'nama' => $s->nama,
            'no_absen' => $s->no_absen,
            'nilai_harian' => $existing->get($s->peserta_id)?->nilai_harian,
            'nilai_uts' => $existing->get($s->peserta_id)?->nilai_uts,
            'nilai_uas' => $existing->get($s->peserta_id)?->nilai_uas,
            'nilai_akhir' => $existing->get($s->peserta_id)?->nilai_akhir,
        ])->all();
    }

    /** Simpan nilai. Terkunci sama seperti nilaiSheet(). */
    public function simpanNilai(JadwalKbm $jadwal, array $rows): void
    {
        $this->guardAbsensiSelesai($jadwal);

        $semester = $this->semesterAktif($jadwal->tahun_pelajaran_id);
        $setting = app(AkademikSettingRepositoryInterface::class)->findByLembaga($jadwal->lembaga_id);

        $bobotH = ($setting?->bobot_harian ?? 40) / 100;
        $bobotU = ($setting?->bobot_uts ?? 30) / 100;
        $bobotA = ($setting?->bobot_uas ?? 30) / 100;

        $upsertRows = [];
        foreach ($rows as $row) {
            $h = isset($row['nilai_harian']) && $row['nilai_harian'] !== '' ? (float) $row['nilai_harian'] : null;
            $u = isset($row['nilai_uts']) && $row['nilai_uts'] !== '' ? (float) $row['nilai_uts'] : null;
            $a = isset($row['nilai_uas']) && $row['nilai_uas'] !== '' ? (float) $row['nilai_uas'] : null;

            $upsertRows[] = [
                'lembaga_id' => $jadwal->lembaga_id,
                'rombel_id' => $jadwal->rombel_id,
                'peserta_id' => (int) $row['peserta_id'],
                'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                'semester_id' => $semester->id,
                'tahun_pelajaran_id' => $jadwal->tahun_pelajaran_id,
                'nilai_harian' => $h,
                'nilai_uts' => $u,
                'nilai_uas' => $a,
                'nilai_akhir' => ($h !== null && $u !== null && $a !== null)
                    ? round(($h * $bobotH) + ($u * $bobotU) + ($a * $bobotA), 2)
                    : null,
            ];
        }

        $this->nilaiRepo->upsert($upsertRows);
        $this->logActivity->log('Input Nilai (Mobile)', "Nilai rombel_id={$jadwal->rombel_id} mapel_id={$jadwal->mata_pelajaran_id} disimpan via mobile (".count($upsertRows).' siswa).');
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    /** Nilai/RPP terkunci sampai sesi absensi untuk rombel+mapel hari ini dibuat. */
    protected function guardAbsensiSelesai(JadwalKbm $jadwal): void
    {
        $tanggal = WaktuSekolah::now()->toDateString();
        $ada = DB::table('absensi')
            ->where('rombel_id', $jadwal->rombel_id)
            ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->whereDate('tanggal', $tanggal)
            ->exists();

        if (! $ada) {
            throw new RuntimeException('Selesaikan absensi siswa terlebih dahulu sebelum membuka nilai.');
        }
    }

    protected function siswaRombel(int $rombelId)
    {
        return DB::table('rombel_siswa')
            ->join('peserta', 'rombel_siswa.peserta_id', '=', 'peserta.id')
            ->where('rombel_siswa.rombel_id', $rombelId)
            ->whereNull('peserta.deleted_at')
            ->orderBy('rombel_siswa.no_absen')
            ->orderBy('peserta.nama_lengkap')
            ->select('peserta.id as peserta_id', 'peserta.nama_lengkap as nama', 'rombel_siswa.no_absen')
            ->get();
    }

    protected function semesterAktif(int $tahunPelajaranId): Semester
    {
        $semester = Semester::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('status', 'aktif')
            ->first();

        if (! $semester) {
            throw new RuntimeException('Semester aktif untuk tahun pelajaran ini belum diatur. Hubungi admin.');
        }

        return $semester;
    }
}
