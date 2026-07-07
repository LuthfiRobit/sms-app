<?php

namespace App\Services\Akademik;

use App\Jobs\KirimAbsensiWhatsappJob;
use App\Models\Akademik\MateriBelajar;
use App\Models\Akademik\PerangkatMengajar;
use App\Models\Akademik\Rpp;
use App\Models\Master\Guru;
use App\Models\Master\JadwalKbm;
use App\Models\Master\Semester;
use App\Models\Peserta\Peserta;
use App\Models\Peserta\PesertaOrangTua;
use App\Repositories\Akademik\AbsensiRepositoryInterface;
use App\Repositories\Akademik\AkademikSettingRepositoryInterface;
use App\Repositories\Akademik\NilaiHarianLogRepositoryInterface;
use App\Repositories\Akademik\NilaiRepositoryInterface;
use App\Services\LogActivityService;
use App\Support\QrToken;
use App\Support\WaktuSekolah;
use Illuminate\Support\Carbon;
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
        protected NilaiHarianLogRepositoryInterface $nilaiHarianLogRepo,
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
        $this->guardJadwalSudahMulai($jadwal);

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
        $this->guardJadwalSudahMulai($jadwal);

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

    /**
     * RPP terstruktur untuk sesi HARI INI + perangkat mengajar legacy +
     * materi ajar disetujui, untuk mapel & rombel jadwal ini.
     *
     * Submateri RPP dianggap 1:1 dengan pertemuan (submateri ke-1 = pertemuan
     * ke-1, dst — urutan kolom `urutan`), jadi guru cuma disodori SATU
     * submateri yang relevan untuk pertemuan hari ini, bukan daftar lengkap
     * semua submateri RPP (yang membingungkan — sebagian besar tidak relevan
     * untuk sesi hari ini). Kalau pertemuan hari ini melebihi jumlah
     * submateri yang direncanakan, jatuh balik ke submateri TERAKHIR
     * (`total_pertemuan` dikirim balik supaya mobile bisa menandai
     * ketidaksesuaian ini ke guru, bukan diam-diam menampilkan yang salah).
     */
    public function materi(JadwalKbm $jadwal): array
    {
        // Perangkat mengajar lama (Silabus/Prota/Prosem/Modul Ajar, dan RPP
        // historis dari sebelum modul RPP terstruktur ada) — jenis 'RPP' baru
        // sudah tidak lagi dibuat lewat sini, tapi baris lama tetap tampil.
        $perangkatMengajar = PerangkatMengajar::where('guru_id', $jadwal->guru_id)
            ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->where('tahun_pelajaran_id', $jadwal->tahun_pelajaran_id)
            ->orderByDesc('id')
            ->get(['id', 'jenis', 'judul', 'deskripsi', 'file_path', 'file_name'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'jenis' => $p->jenis,
                'judul' => $p->judul,
                'deskripsi' => $p->deskripsi,
                'file_url' => $p->file_path ? asset('storage/'.$p->file_path) : null,
                'file_name' => $p->file_name,
            ]);

        $tanggal = WaktuSekolah::now()->toDateString();
        $pertemuanHariIni = $this->hitungPertemuanHariIni($jadwal, $tanggal);

        // RPP terstruktur — hanya yang sudah disetujui yang boleh tampil ke
        // guru. Kalau guru punya beberapa RPP disetujui utk mapel yang sama
        // (topik berbeda sepanjang tahun), yang paling baru (id terbesar)
        // dianggap yang sedang berjalan.
        $rppAktif = $this->findRppAktif($jadwal, ['nilaiPoin.poin', 'inti.sintaks', 'submateri', 'modelPembelajaran']);

        $rppHariIni = $rppAktif ? $this->buildRppHariIni($rppAktif, $pertemuanHariIni) : null;

        $materi = MateriBelajar::where('guru_id', $jadwal->guru_id)
            ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->where('status', 'disetujui')
            ->where(function ($q) use ($jadwal) {
                $q->where('rombel_id', $jadwal->rombel_id)->orWhereNull('rombel_id');
            })
            ->orderByDesc('tanggal')
            ->get(['id', 'judul', 'deskripsi', 'file_path', 'file_name', 'url_eksternal', 'tanggal', 'pertemuan_ke'])
            ->map(fn ($m) => [
                'id' => $m->id,
                'judul' => $m->judul,
                'deskripsi' => $m->deskripsi,
                'file_url' => $m->file_path ? asset('storage/'.$m->file_path) : null,
                'file_name' => $m->file_name,
                'url_eksternal' => $m->url_eksternal,
                'tanggal' => $m->tanggal,
                'pertemuan_ke' => $m->pertemuan_ke,
            ]);

        return [
            'rpp_hari_ini' => $rppHariIni,
            'perangkat_mengajar' => $perangkatMengajar,
            'materi' => $materi,
            'pertemuan_hari_ini' => $pertemuanHariIni,
        ];
    }

    /**
     * Rangkai satu RPP + submateri pertemuan hari ini jadi struktur ringkas
     * siap-tampil di HP guru — cukup untuk pegangan mengajar (tujuan,
     * indikator, urutan kegiatan, pertanyaan pemantik), bukan dump seluruh
     * poin (dokumen lengkap termasuk rubrik/soal tetap di PDF, lebih nyaman
     * dibaca di sana daripada di-scroll di layar kecil).
     */
    protected function buildRppHariIni(Rpp $rpp, int $pertemuanHariIni): array
    {
        [$submateriTeks, $totalPertemuan, $pertemuanEfektif] = $this->pilihSubmateri($rpp->submateri, $pertemuanHariIni);

        $intiByFase = $rpp->inti->groupBy(fn ($i) => $i->sintaks?->meta_fase);
        $inti = collect(['Memahami', 'Mengaplikasi', 'Merefleksi'])
            ->map(function ($fase) use ($intiByFase) {
                $items = ($intiByFase[$fase] ?? collect())->sortBy('urutan')->values();

                if ($items->isEmpty()) {
                    return null;
                }

                return [
                    'fase' => $fase,
                    'sintaks' => $items->map(fn ($i) => [
                        'nama' => $i->sintaks?->nama_sintaks,
                        'kegiatan' => $i->konten ?? [],
                    ])->values()->all(),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $rpp->id,
            'materi' => $rpp->materi,
            'submateri' => $submateriTeks,
            'pertemuan_ke' => $pertemuanEfektif,
            'total_pertemuan' => $totalPertemuan,
            'fase_kelas' => $rpp->fase_kelas,
            'alokasi_waktu' => $rpp->alokasi_waktu,
            'model_pembelajaran' => $rpp->modelPembelajaran?->nama,
            'tujuan_pembelajaran' => $this->nilaiPoinByKode($rpp, 'tujuan_pembelajaran')?->value_teks,
            'iktp' => $this->nilaiPoinByKode($rpp, 'iktp')?->value_json ?? [],
            'pendahuluan' => $this->nilaiPoinByKode($rpp, 'pendahuluan')?->value_json ?? [],
            'inti' => $inti,
            'penutup' => $this->nilaiPoinByKode($rpp, 'penutup')?->value_json ?? [],
            'pertanyaan_pemantik' => $this->nilaiPoinByKode($rpp, 'formatif_awal')?->value_json ?? [],
            'file_url' => $rpp->file_path ? asset('storage/'.$rpp->file_path) : null,
            'file_name' => $rpp->file_name,
        ];
    }

    protected function nilaiPoinByKode(Rpp $rpp, string $kode)
    {
        return $rpp->nilaiPoin->first(fn ($v) => $v->poin?->kode === $kode);
    }

    /**
     * Submateri dianggap 1:1 dengan pertemuan (submateri ke-N = pertemuan
     * ke-N) — dipakai bareng oleh buildRppHariIni() (tampilan mobile) dan
     * kirimNotifikasiSelesai() (isi pesan WhatsApp) supaya keduanya SELALU
     * menunjuk submateri yang sama untuk pertemuan yang sama.
     *
     * @return array{0: ?string, 1: int, 2: int} [teks submateri, total pertemuan, pertemuan efektif]
     */
    protected function pilihSubmateri($submateriList, int $pertemuanHariIni): array
    {
        $total = $submateriList->count();

        if ($total === 0) {
            // RPP satu sesi (guru tidak memecah submateri) — berlaku untuk pertemuan manapun.
            return [null, 0, $pertemuanHariIni];
        }

        $index = min($pertemuanHariIni, $total) - 1;
        $terpilih = $submateriList->values()->get($index);

        return [$terpilih?->teks, $total, $terpilih?->urutan ?? $pertemuanHariIni];
    }

    /** RPP disetujui paling baru untuk guru+mapel+tahun ini — dianggap yang sedang aktif diajarkan. */
    protected function findRppAktif(JadwalKbm $jadwal, array $with = ['submateri']): ?Rpp
    {
        return Rpp::where('guru_id', $jadwal->guru_id)
            ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->where('tahun_pelajaran_id', $jadwal->tahun_pelajaran_id)
            ->where('status', 'disetujui')
            ->with($with)
            ->orderByDesc('id')
            ->first();
    }

    /** Hitung "pertemuan ke berapa hari ini" dari jumlah sesi absensi sebelumnya. */
    protected function hitungPertemuanHariIni(JadwalKbm $jadwal, string $tanggal): int
    {
        $pastMeetings = DB::table('absensi')
            ->where('rombel_id', $jadwal->rombel_id)
            ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->whereDate('tanggal', '<', $tanggal)
            ->count();

        return $pastMeetings + 1;
    }

    /**
     * Kirim notifikasi WhatsApp ke wali murid tiap siswa di kelas ini,
     * melaporkan status kehadiran (+ submateri & nilai kalau ada) untuk
     * sesi HARI INI. Dipicu eksplisit oleh guru (tombol "Selesai Mengajar")
     * — sengaja BUKAN otomatis saat absensi disimpan, karena nilai baru
     * mungkin diisi belakangan (lihat tambahNilaiHarian()), dan supaya guru
     * sendiri yang menentukan kapan sesi benar-benar selesai sebelum pesan
     * terkirim ke orang tua siswa.
     *
     * @return array{terkirim: int, gagal_kirim: int, tanpa_nomor: int, total_siswa: int}
     */
    public function kirimNotifikasiSelesai(JadwalKbm $jadwal): array
    {
        $tanggal = WaktuSekolah::now()->toDateString();
        $absensi = $this->absensiRepo->findByRombelMapelTanggal($jadwal->rombel_id, $jadwal->mata_pelajaran_id, $tanggal);

        if (! $absensi) {
            throw new RuntimeException('Selesaikan absensi siswa terlebih dahulu sebelum mengirim notifikasi.');
        }

        if ($absensi->notifikasi_terkirim_at) {
            $waktu = $absensi->notifikasi_terkirim_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm');
            throw new RuntimeException("Notifikasi untuk sesi ini sudah pernah dikirim pada {$waktu}.");
        }

        $absensi->load('detail.peserta.orangTua');

        $pertemuanKe = $this->hitungPertemuanHariIni($jadwal, $tanggal);
        $rpp = $this->findRppAktif($jadwal);
        [$submateri] = $this->pilihSubmateri($rpp?->submateri ?? collect(), $pertemuanKe);

        $semester = $this->semesterAktif($jadwal->tahun_pelajaran_id);
        $nilaiHariIni = DB::table('nilai_harian_log')
            ->where('rombel_id', $jadwal->rombel_id)
            ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->where('semester_id', $semester->id)
            ->where('tanggal', $tanggal)
            ->orderByDesc('id')
            ->get()
            ->unique('peserta_id')
            ->keyBy('peserta_id');

        $tanggalFormatted = Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY');

        $terkirim = 0;
        $gagalKirim = 0;
        $tanpaNomor = 0;

        foreach ($absensi->detail as $detail) {
            $peserta = $detail->peserta;
            if (! $peserta) {
                continue;
            }

            $wali = $this->resolveWaliKontak($peserta);
            if (! $wali) {
                $tanpaNomor++;
                continue;
            }

            $nilai = $detail->status === 'hadir' ? $nilaiHariIni->get($peserta->id)?->nilai : null;

            $pesan = $this->buildPesanAbsensi(
                $wali->nama,
                $peserta->nama_lengkap,
                $detail->status,
                $tanggalFormatted,
                $detail->status === 'hadir' ? $submateri : null,
                $nilai !== null ? (float) $nilai : null,
            );

            // dispatchSync() (BUKAN dispatch()+onQueue) — kirim langsung saat
            // ini juga, tidak bergantung queue worker terpisah yang harus
            // selalu aktif (lihat docblock KirimAbsensiWhatsappJob). Dibungkus
            // try/catch PER SISWA supaya satu nomor bermasalah tidak
            // menggagalkan pengiriman ke siswa lain dalam kelas yang sama.
            try {
                KirimAbsensiWhatsappJob::dispatchSync($peserta->id, $peserta->nama_lengkap, $wali->no_hp, $pesan);
                $terkirim++;
            } catch (\Throwable $e) {
                $gagalKirim++;
            }
        }

        $absensi->update(['notifikasi_terkirim_at' => now()]);

        $this->logActivity->log(
            'Kirim Notifikasi Absensi (Mobile)',
            "Notifikasi WA rombel_id={$jadwal->rombel_id} mapel_id={$jadwal->mata_pelajaran_id} tanggal={$tanggal}: {$terkirim} terkirim, {$gagalKirim} gagal, {$tanpaNomor} tanpa nomor HP wali."
        );

        return [
            'terkirim' => $terkirim,
            'gagal_kirim' => $gagalKirim,
            'tanpa_nomor' => $tanpaNomor,
            'total_siswa' => $absensi->detail->count(),
        ];
    }

    /** Prioritas kontak: wali > ayah > ibu — kirim ke SATU nomor saja per siswa (hemat, tidak dobel). */
    protected function resolveWaliKontak(Peserta $peserta): ?PesertaOrangTua
    {
        $adaNoHp = fn ($tipe) => $peserta->orangTua->first(fn ($o) => $o->tipe === $tipe && filled($o->no_hp));

        return $adaNoHp(PesertaOrangTua::TIPE_WALI)
            ?? $adaNoHp(PesertaOrangTua::TIPE_AYAH)
            ?? $adaNoHp(PesertaOrangTua::TIPE_IBU);
    }

    /** Susun teks pesan WhatsApp sesuai status kehadiran. */
    protected function buildPesanAbsensi(
        string $namaWali,
        string $namaSiswa,
        string $status,
        string $tanggalFormatted,
        ?string $submateri,
        ?float $nilai,
    ): string {
        $isi = match ($status) {
            'hadir' => "Disampaikan kepada Bapak/Ibu {$namaWali}, bahwa ananda {$namaSiswa} telah HADIR mengikuti pembelajaran di kelas pada {$tanggalFormatted}"
                .($submateri ? " dengan materi \"{$submateri}\"" : '')
                .($nilai !== null ? ' dengan nilai '.$this->formatNilaiTampil($nilai) : '')
                .'. Terima kasih.',
            'sakit' => "Disampaikan kepada Bapak/Ibu {$namaWali}, bahwa ananda {$namaSiswa} tidak dapat mengikuti pembelajaran di kelas pada {$tanggalFormatted} dikarenakan SAKIT. Semoga lekas sembuh. Terima kasih.",
            'izin' => "Disampaikan kepada Bapak/Ibu {$namaWali}, bahwa ananda {$namaSiswa} tidak dapat mengikuti pembelajaran di kelas pada {$tanggalFormatted} dikarenakan IZIN. Terima kasih.",
            default => "Disampaikan kepada Bapak/Ibu {$namaWali}, bahwa ananda {$namaSiswa} TIDAK HADIR (Alpa) mengikuti pembelajaran di kelas pada {$tanggalFormatted} tanpa keterangan. Mohon menjadi perhatian Bapak/Ibu. Terima kasih.",
        };

        return "Assalamu'alaikum Wr. Wb.\n{$isi}\nWassalamu'alaikum Wr. Wb.";
    }

    /** "93.00" -> "93", "88.50" -> "88.5" — nilai bulat tidak perlu tampil ".00". */
    protected function formatNilaiTampil(float $nilai): string
    {
        return rtrim(rtrim(sprintf('%.2f', $nilai), '0'), '.');
    }

    /**
     * Lembar nilai UTS/UAS untuk jadwal ini. `nilai_harian` ikut ditampilkan
     * (baca-saja, sebagai konteks) tapi TIDAK lagi diedit dari sini — nilai
     * harian sekarang berbasis riwayat per sesi, lihat nilaiHarianRiwayat()/
     * tambahNilaiHarian(). Terkunci (RuntimeException) jika absensi siswa
     * untuk sesi ini belum dibuat.
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

    /**
     * Simpan nilai UTS/UAS SAJA — nilai_harian tidak disentuh di sini (lihat
     * tambahNilaiHarian()). Terkunci sama seperti nilaiSheet().
     */
    public function simpanNilai(JadwalKbm $jadwal, array $rows): void
    {
        $this->guardAbsensiSelesai($jadwal);

        $semester = $this->semesterAktif($jadwal->tahun_pelajaran_id);
        $setting = app(AkademikSettingRepositoryInterface::class)->findByLembaga($jadwal->lembaga_id);

        $bobotH = ($setting?->bobot_harian ?? 40) / 100;
        $bobotU = ($setting?->bobot_uts ?? 30) / 100;
        $bobotA = ($setting?->bobot_uas ?? 30) / 100;

        $existing = $this->nilaiRepo->getByRombelMapelSemester($jadwal->rombel_id, $jadwal->mata_pelajaran_id, $semester->id)
            ->keyBy('peserta_id');

        $upsertRows = [];
        foreach ($rows as $row) {
            $pesertaId = (int) $row['peserta_id'];
            $existingRow = $existing->get($pesertaId);

            // nilai_harian & catatan sengaja dipertahankan dari nilai lama —
            // upsert() MySQL memakai NULL utk kolom yg tidak ikut disertakan
            // di baris insert (ON DUPLICATE KEY UPDATE col=VALUES(col)), jadi
            // tanpa ini nilai harian yg sudah terhitung dari riwayat bisa
            // tertimpa NULL setiap kali guru simpan UTS/UAS.
            $h = $existingRow?->nilai_harian;
            $u = isset($row['nilai_uts']) && $row['nilai_uts'] !== '' ? (float) $row['nilai_uts'] : null;
            $a = isset($row['nilai_uas']) && $row['nilai_uas'] !== '' ? (float) $row['nilai_uas'] : null;

            $upsertRows[] = [
                'lembaga_id' => $jadwal->lembaga_id,
                'rombel_id' => $jadwal->rombel_id,
                'peserta_id' => $pesertaId,
                'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                'semester_id' => $semester->id,
                'tahun_pelajaran_id' => $jadwal->tahun_pelajaran_id,
                'nilai_harian' => $h,
                'nilai_uts' => $u,
                'nilai_uas' => $a,
                'nilai_akhir' => ($h !== null && $u !== null && $a !== null)
                    ? round(($h * $bobotH) + ($u * $bobotU) + ($a * $bobotA), 2)
                    : null,
                'catatan' => $existingRow?->catatan,
            ];
        }

        $this->nilaiRepo->upsert($upsertRows);
        $this->logActivity->log('Input Nilai UTS/UAS (Mobile)', "Nilai UTS/UAS rombel_id={$jadwal->rombel_id} mapel_id={$jadwal->mata_pelajaran_id} disimpan via mobile (".count($upsertRows).' siswa).');
    }

    /**
     * Riwayat nilai harian (satu baris per sesi/tugas) + rata-rata berjalan
     * per siswa, untuk rombel+mapel+semester jadwal ini. Terkunci sama
     * seperti nilaiSheet().
     */
    public function nilaiHarianRiwayat(JadwalKbm $jadwal): array
    {
        $this->guardAbsensiSelesai($jadwal);

        $semester = $this->semesterAktif($jadwal->tahun_pelajaran_id);
        $siswa = $this->siswaRombel($jadwal->rombel_id);
        $semuaLog = $this->nilaiHarianLogRepo
            ->getByRombelMapelSemester($jadwal->rombel_id, $jadwal->mata_pelajaran_id, $semester->id)
            ->groupBy('peserta_id');
        $rataRata = $this->nilaiHarianLogRepo
            ->averagesByRombelMapelSemester($jadwal->rombel_id, $jadwal->mata_pelajaran_id, $semester->id);

        return $siswa->map(fn ($s) => [
            'peserta_id' => $s->peserta_id,
            'nama' => $s->nama,
            'no_absen' => $s->no_absen,
            'rata_rata' => $rataRata->get($s->peserta_id)?->rata_rata !== null
                ? round((float) $rataRata->get($s->peserta_id)->rata_rata, 2)
                : null,
            'riwayat' => ($semuaLog->get($s->peserta_id) ?? collect())->map(fn ($log) => [
                'id' => $log->id,
                'tanggal' => $log->tanggal->toDateString(),
                'pertemuan_ke' => $log->pertemuan_ke,
                'keterangan' => $log->keterangan,
                'nilai' => $log->nilai,
            ])->values()->all(),
        ])->all();
    }

    /**
     * Tambah nilai harian baru untuk sesi HARI INI — MENAMBAH baris riwayat
     * (bukan menimpa). `nilai.nilai_harian`/`nilai_akhir` otomatis dihitung
     * ulang setelahnya (rata-rata seluruh riwayat). Terkunci sama seperti
     * nilaiSheet().
     */
    public function tambahNilaiHarian(JadwalKbm $jadwal, array $rows, ?string $keteranganDefault): void
    {
        $this->guardAbsensiSelesai($jadwal);

        $tanggal = WaktuSekolah::now()->toDateString();
        $semester = $this->semesterAktif($jadwal->tahun_pelajaran_id);

        $pastMeetings = DB::table('absensi')
            ->where('rombel_id', $jadwal->rombel_id)
            ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
            ->whereDate('tanggal', '<', $tanggal)
            ->count();
        $pertemuanKe = $pastMeetings + 1;

        $now = now();
        $logRows = [];
        $pesertaIds = [];
        foreach ($rows as $row) {
            $pesertaId = (int) $row['peserta_id'];
            $pesertaIds[] = $pesertaId;
            $logRows[] = [
                'lembaga_id' => $jadwal->lembaga_id,
                'rombel_id' => $jadwal->rombel_id,
                'peserta_id' => $pesertaId,
                'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                'semester_id' => $semester->id,
                'tahun_pelajaran_id' => $jadwal->tahun_pelajaran_id,
                'tanggal' => $tanggal,
                'pertemuan_ke' => $pertemuanKe,
                'keterangan' => $row['keterangan'] ?? $keteranganDefault,
                'nilai' => (float) $row['nilai'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($logRows)) {
            throw new RuntimeException('Tidak ada nilai yang diisi.');
        }

        $this->nilaiHarianLogRepo->createMany($logRows);
        $this->recalcNilaiHarian($jadwal, $semester, array_unique($pesertaIds));

        $this->logActivity->log(
            'Input Nilai Harian (Mobile)',
            "Nilai harian rombel_id={$jadwal->rombel_id} mapel_id={$jadwal->mata_pelajaran_id} pertemuan ke-{$pertemuanKe} disimpan via mobile (".count($logRows).' siswa).'
        );
    }

    /** Hitung ulang nilai_harian (rata-rata riwayat) + nilai_akhir untuk peserta yang baru dapat entri harian. */
    protected function recalcNilaiHarian(JadwalKbm $jadwal, Semester $semester, array $pesertaIds): void
    {
        $rataRata = $this->nilaiHarianLogRepo
            ->averagesByRombelMapelSemester($jadwal->rombel_id, $jadwal->mata_pelajaran_id, $semester->id);
        $existing = $this->nilaiRepo
            ->getByRombelMapelSemester($jadwal->rombel_id, $jadwal->mata_pelajaran_id, $semester->id)
            ->keyBy('peserta_id');
        $setting = app(AkademikSettingRepositoryInterface::class)->findByLembaga($jadwal->lembaga_id);

        $bobotH = ($setting?->bobot_harian ?? 40) / 100;
        $bobotU = ($setting?->bobot_uts ?? 30) / 100;
        $bobotA = ($setting?->bobot_uas ?? 30) / 100;

        $upsertRows = [];
        foreach ($pesertaIds as $pesertaId) {
            $existingRow = $existing->get($pesertaId);
            $h = $rataRata->get($pesertaId)?->rata_rata !== null
                ? round((float) $rataRata->get($pesertaId)->rata_rata, 2)
                : null;
            $u = $existingRow?->nilai_uts;
            $a = $existingRow?->nilai_uas;

            $upsertRows[] = [
                'lembaga_id' => $jadwal->lembaga_id,
                'rombel_id' => $jadwal->rombel_id,
                'peserta_id' => $pesertaId,
                'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                'semester_id' => $semester->id,
                'tahun_pelajaran_id' => $jadwal->tahun_pelajaran_id,
                'nilai_harian' => $h,
                'nilai_uts' => $u,
                'nilai_uas' => $a,
                'nilai_akhir' => ($h !== null && $u !== null && $a !== null)
                    ? round(($h * $bobotH) + ($u * $bobotU) + ($a * $bobotA), 2)
                    : null,
                'catatan' => $existingRow?->catatan,
            ];
        }

        $this->nilaiRepo->upsert($upsertRows);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    /**
     * Tolak absensi (form maupun scan QR) kalau jadwal ini belum waktunya
     * dimulai — mencegah guru mencatat kehadiran untuk sesi yang belum
     * berlangsung (status 'akan_datang' di listing Jadwal Hari Ini). Guard
     * ini yang sesungguhnya menegakkan aturan, bukan sekadar menonaktifkan
     * tombol di mobile (yang cuma UX, bisa dilewati kalau request dikirim
     * langsung ke API).
     */
    protected function guardJadwalSudahMulai(JadwalKbm $jadwal): void
    {
        $sekarang = WaktuSekolah::now();
        $mulai = Carbon::parse($sekarang->toDateString().' '.$jadwal->jam_mulai, config('sekolah.timezone'));

        if ($sekarang->lt($mulai)) {
            $jamMulai = substr((string) $jadwal->jam_mulai, 0, 5);
            throw new RuntimeException("Jadwal ini belum dimulai (mulai pukul {$jamMulai}). Absensi baru bisa diisi setelah jam pelajaran dimulai.");
        }
    }

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
