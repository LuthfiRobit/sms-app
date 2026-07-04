<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data pendukung alur "Kelas" di aplikasi mobile guru: rombel per jenjang,
 * siswa di tiap rombel, dan jadwal mengajar (jadwal_kbm) lintas 7 lembaga.
 *
 * Tanpa data ini, tab Jadwal & Beranda di mobile selalu kosong dan alur
 * Kelas (absensi siswa → materi → nilai) tidak bisa dites end-to-end.
 *
 * Idempoten: rombel/mapel/jadwal yang sudah ada tidak diduplikasi — aman
 * dijalankan ulang kapan saja untuk melengkapi lembaga yang masih kosong.
 *
 * Siswa dibuat sebagai `peserta` + `rombel_siswa` DAN diberi record
 * `pendaftaran` berstatus `siswa_tetap` (dengan jalur_pendaftaran pertama
 * milik lembaga tersebut) supaya juga muncul secara wajar di menu
 * "Data Siswa" admin web — bukan cuma "keperluan mobile" saja.
 */
class KelasMobileDemoSeeder extends Seeder
{
    private array $namaDepanL = [
        'Ahmad', 'Muhammad', 'Bagus', 'Rizki', 'Fajar', 'Dimas', 'Ilham', 'Yusuf',
        'Reza', 'Aditya', 'Bayu', 'Fauzan', 'Irfan', 'Rafi', 'Zaki', 'Wahyu',
        'Rendra', 'Fikri', 'Galih', 'Hafiz',
    ];

    private array $namaDepanP = [
        'Siti', 'Nur', 'Putri', 'Dewi', 'Aisyah', 'Salsabila', 'Zahra', 'Fitri',
        'Amelia', 'Rania', 'Salma', 'Naila', 'Alya', 'Khoirunnisa', 'Widya',
        'Intan', 'Yuni', 'Anisa', 'Laila', 'Rahma',
    ];

    private array $namaBelakang = [
        'Pratama', 'Saputra', 'Wijaya', 'Ramadhan', 'Santoso', 'Firmansyah',
        'Kurniawan', 'Setiawan', 'Nugroho', 'Hidayat', 'Maulana', 'Susanto',
        'Wibowo', 'Rahmawati', 'Anggraini', 'Lestari', 'Puspita', 'Handayani',
        'Wardani', 'Utami',
    ];

    /** Mapel dasar per jenjang — dipakai hanya jika lembaga belum punya mapel sama sekali. */
    private array $mapelByJenis = [
        'MI' => [
            ['kode' => 'MTK', 'nama' => 'Matematika'],
            ['kode' => 'BIN', 'nama' => 'Bahasa Indonesia'],
            ['kode' => 'IPA', 'nama' => 'Ilmu Pengetahuan Alam'],
            ['kode' => 'IPS', 'nama' => 'Ilmu Pengetahuan Sosial'],
            ['kode' => 'PAI', 'nama' => 'Pendidikan Agama Islam'],
            ['kode' => 'BIG', 'nama' => 'Bahasa Inggris'],
        ],
        'MTs' => [
            ['kode' => 'MTK', 'nama' => 'Matematika'],
            ['kode' => 'BIN', 'nama' => 'Bahasa Indonesia'],
            ['kode' => 'BIG', 'nama' => 'Bahasa Inggris'],
            ['kode' => 'IPA', 'nama' => 'Ilmu Pengetahuan Alam'],
            ['kode' => 'FIQ', 'nama' => 'Fiqih'],
            ['kode' => 'BAR', 'nama' => 'Bahasa Arab'],
        ],
        'SMP' => [
            ['kode' => 'MTK', 'nama' => 'Matematika'],
            ['kode' => 'BIN', 'nama' => 'Bahasa Indonesia'],
            ['kode' => 'BIG', 'nama' => 'Bahasa Inggris'],
            ['kode' => 'IPA', 'nama' => 'Ilmu Pengetahuan Alam'],
            ['kode' => 'PPK', 'nama' => 'Pendidikan Pancasila'],
            ['kode' => 'PAI', 'nama' => 'Pendidikan Agama Islam'],
        ],
        'MA' => [
            ['kode' => 'MTK', 'nama' => 'Matematika'],
            ['kode' => 'BIN', 'nama' => 'Bahasa Indonesia'],
            ['kode' => 'BIG', 'nama' => 'Bahasa Inggris'],
            ['kode' => 'FIS', 'nama' => 'Fisika'],
            ['kode' => 'EKO', 'nama' => 'Ekonomi'],
            ['kode' => 'BAR', 'nama' => 'Bahasa Arab'],
        ],
    ];

    private array $hariUrutan = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function run(): void
    {
        $tahunAktif = DB::table('tahun_pelajaran')->where('status', 'aktif')->first();

        if (! $tahunAktif) {
            $this->command->warn('KelasMobileDemoSeeder: tidak ada tahun pelajaran aktif, dilewati.');

            return;
        }

        $tahunAktifId = $tahunAktif->id;
        preg_match('/\d{4}/', $tahunAktif->nama, $match);
        $tahunAngka = $match[0] ?? date('Y');

        $rombelDibuat = 0;
        $siswaDibuat = 0;
        $jadwalDibuat = 0;

        foreach (DB::table('lembaga')->orderBy('id')->get() as $lembaga) {
            $this->pastikanMapel($lembaga);

            $jurusanId = DB::table('jurusan')->where('lembaga_id', $lembaga->id)->value('id');
            $guruIds = DB::table('guru')->where('lembaga_id', $lembaga->id)->where('status', 'aktif')->pluck('id')->all();
            $mapelIds = DB::table('mata_pelajaran')->where('lembaga_id', $lembaga->id)->pluck('id')->all();
            $jalurId = DB::table('jalur_pendaftaran')
                ->join('pembukaan_ppdb', 'pembukaan_ppdb.id', '=', 'jalur_pendaftaran.pembukaan_ppdb_id')
                ->where('pembukaan_ppdb.lembaga_id', $lembaga->id)
                ->value('jalur_pendaftaran.id');

            if (empty($guruIds) || empty($mapelIds) || ! $jalurId) {
                continue;
            }

            foreach ($this->tingkatUntukJenis($lembaga->jenis) as $tingkat) {
                $rombel = DB::table('rombel')->where('lembaga_id', $lembaga->id)->where('tingkat', $tingkat)->first();

                if (! $rombel) {
                    $rombelId = DB::table('rombel')->insertGetId([
                        'lembaga_id' => $lembaga->id,
                        'tahun_pelajaran_id' => $tahunAktifId,
                        'jurusan_id' => $jurusanId,
                        'tingkat' => $tingkat,
                        // Tanpa tingkat di label — tingkat sudah kolom terpisah, dan
                        // semua pemanggil (mobile, blade) menggabungkan "{tingkat} {nama}" sendiri.
                        'nama' => 'A',
                        'kapasitas' => 30,
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $rombelDibuat++;
                } else {
                    $rombelId = $rombel->id;
                }

                if (! DB::table('rombel_siswa')->where('rombel_id', $rombelId)->exists()) {
                    $siswaDibuat += $this->buatSiswa($rombelId, $lembaga->id, $jalurId, $tahunAktifId, $tahunAngka);
                }

                if (! DB::table('jadwal_kbm')->where('rombel_id', $rombelId)->exists()) {
                    $jadwalDibuat += $this->buatJadwal($lembaga->id, $tahunAktifId, $rombelId, $mapelIds, $guruIds);
                }
            }
        }

        $this->command->info("KelasMobileDemoSeeder: {$rombelDibuat} rombel, {$siswaDibuat} siswa, {$jadwalDibuat} jadwal_kbm dibuat.");
    }

    private function tingkatUntukJenis(string $jenis): array
    {
        return match ($jenis) {
            'MI' => [1, 2, 3, 4, 5, 6],
            'MTs', 'SMP' => [7, 8, 9],
            'MA', 'SMK' => [10, 11, 12],
            default => [],
        };
    }

    private function pastikanMapel(object $lembaga): void
    {
        if (DB::table('mata_pelajaran')->where('lembaga_id', $lembaga->id)->exists()) {
            return;
        }

        $daftar = $this->mapelByJenis[$lembaga->jenis] ?? $this->mapelByJenis['SMP'];

        foreach ($daftar as $i => $m) {
            DB::table('mata_pelajaran')->insert([
                'lembaga_id' => $lembaga->id,
                'kode' => $m['kode'],
                'nama' => $m['nama'],
                'urutan' => $i + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function buatSiswa(int $rombelId, int $lembagaId, int $jalurId, int $tahunId, string $tahunAngka, int $jumlah = 20): int
    {
        $rows = [];

        for ($i = 1; $i <= $jumlah; $i++) {
            $isPria = mt_rand(0, 1) === 0;
            $depan = $isPria ? $this->namaDepanL[array_rand($this->namaDepanL)] : $this->namaDepanP[array_rand($this->namaDepanP)];
            $belakang = $this->namaBelakang[array_rand($this->namaBelakang)];

            $pesertaId = DB::table('peserta')->insertGetId([
                'nama_lengkap' => "{$depan} {$belakang}",
                'jenis_kelamin' => $isPria ? 'L' : 'P',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $rows[] = [
                'rombel_id' => $rombelId,
                'peserta_id' => $pesertaId,
                'no_absen' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Supaya siswa juga muncul di menu "Data Siswa" admin web, yang
            // mensyaratkan pendaftaran.status = siswa_tetap.
            DB::table('pendaftaran')->insert([
                'no_pendaftaran' => 'PPDB'.$tahunAngka.str_pad((string) $pesertaId, 5, '0', STR_PAD_LEFT),
                'peserta_id' => $pesertaId,
                'jalur_pendaftaran_id' => $jalurId,
                'tahun_pelajaran_id' => $tahunId,
                'lembaga_id' => $lembagaId,
                'status' => 'siswa_tetap',
                'tanggal_daftar' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('rombel_siswa')->insert($rows);

        return count($rows);
    }

    private function buatJadwal(int $lembagaId, int $tahunId, int $rombelId, array $mapelIds, array $guruIds): int
    {
        $jumlah = min(count($mapelIds), count($this->hariUrutan));
        $rows = [];

        for ($i = 0; $i < $jumlah; $i++) {
            $rows[] = [
                'lembaga_id' => $lembagaId,
                'tahun_pelajaran_id' => $tahunId,
                'rombel_id' => $rombelId,
                'guru_id' => $guruIds[$i % count($guruIds)],
                'mata_pelajaran_id' => $mapelIds[$i],
                'hari' => $this->hariUrutan[$i],
                'jam_mulai' => '07:00:00',
                'jam_selesai' => '08:30:00',
                'jam_ke' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('jadwal_kbm')->insert($rows);

        return count($rows);
    }
}
