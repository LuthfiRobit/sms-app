<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Menanamkan data PPDB realistis untuk 6 lembaga selain SMK Ma'arif Gending.
 * (SMK sudah ditangani oleh PpdbDemoTestDataSeeder.)
 *
 * Hierarki data:
 *   PembukaanPpdb → JalurPendaftaran → Jadwal / Syarat / Biaya / Formulir / KuotaJurusan
 */
class PpdbAllLembagaSeeder extends Seeder
{
    private int $tahunId;

    // =========================================================================
    // ENTRY POINT
    // =========================================================================

    public function run(): void
    {
        $this->tahunId = DB::table('tahun_pelajaran')->where('kode_tahun', '2026/2027')->value('id');

        if (! $this->tahunId) {
            $this->command->error('Tahun Pelajaran 2026/2027 tidak ditemukan. Jalankan TahunPelajaranSeeder terlebih dahulu.');
            return;
        }

        $this->command->info('🏫 Menanamkan data PPDB per lembaga...');

        $this->seedMinuKraksaan();
        $this->seedMinuSunanAmpelMaron();
        $this->seedMtsNuSunanAmpelMaron();
        $this->seedMtsNuMaron();
        $this->seedSmpMaarifKraksaan();
        $this->seedManuMaron();

        $this->command->info('✅ Selesai — data PPDB 6 lembaga berhasil ditanamkan.');
    }

    // =========================================================================
    // PER-LEMBAGA SEEDERS
    // =========================================================================

    private function seedMinuKraksaan(): void
    {
        $lembagaId = $this->getLembagaId('MINU-KRK');

        $pembukaanId = $this->createPembukaan($lembagaId, [
            'nama'    => 'Penerimaan Santri Baru (PSB) 2026/2027',
            'mulai'   => '2026-03-01',
            'selesai' => '2026-04-30',
            'status'  => 'tutup',
        ]);

        $syarat = $this->syaratMI();
        $fields = $this->formulirMI();

        // Jalur 1: Reguler
        $j1 = $this->createJalur($pembukaanId, ['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'kuota' => 30, 'urutan' => 1]);
        $this->createJadwal($j1, $this->jadwalLewat('2026-03-01', '2026-04-30', '2026-05-01', '2026-05-05', '2026-05-06', '2026-05-10', '2026-05-11', '2026-05-20'));
        $this->createSyarat($j1, $syarat);
        $this->createBiaya($j1, 'Biaya Pendaftaran PSB Reguler', 75000);
        $this->createFormulir($j1, 'Formulir Data Tambahan MI', $fields);
        $this->createKuotaJurusan($j1, ['MINU-KRK-REG' => 30]);

        // Jalur 2: Beasiswa Tahfidz
        $j2 = $this->createJalur($pembukaanId, ['kode' => 'TAHFIDZ', 'nama' => 'Beasiswa Tahfidz Al-Quran', 'kuota' => 5, 'deskripsi' => 'Gratis untuk calon santri penghafal Al-Quran min. 1 juz', 'urutan' => 2]);
        $this->createJadwal($j2, $this->jadwalLewat('2026-03-01', '2026-04-30', '2026-05-01', '2026-05-05', '2026-05-06', '2026-05-10', '2026-05-11', '2026-05-20'));
        $this->createSyarat($j2, array_merge($syarat, [
            ['nama' => 'Surat Keterangan Hafalan', 'tipe' => 'dokumen', 'wajib' => true, 'keterangan' => 'Dari ustadz/guru ngaji yang bersangkutan', 'urutan' => 5],
        ]));
        $this->createBiaya($j2, 'Beasiswa Tahfidz (Gratis)', 0, 'Seluruh biaya ditanggung lembaga untuk jalur tahfidz');
        $this->createFormulir($j2, 'Formulir Data Tambahan MI', $fields);
        $this->createKuotaJurusan($j2, ['MINU-KRK-REG' => 5]);

        $this->command->info('  ✓ MINU Kraksaan: PSB 2026/2027 (tutup) — 2 jalur');
    }

    private function seedMinuSunanAmpelMaron(): void
    {
        $lembagaId = $this->getLembagaId('MINU-SAM');

        $pembukaanId = $this->createPembukaan($lembagaId, [
            'nama'    => 'Penerimaan Santri Baru (PSB) 2026/2027',
            'mulai'   => '2026-03-01',
            'selesai' => '2026-04-30',
            'status'  => 'tutup',
        ]);

        $syarat = $this->syaratMI();
        $fields = $this->formulirMI();

        // Jalur 1: Reguler
        $j1 = $this->createJalur($pembukaanId, ['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'kuota' => 25, 'urutan' => 1]);
        $this->createJadwal($j1, $this->jadwalLewat('2026-03-01', '2026-04-30', '2026-05-01', '2026-05-05', '2026-05-06', '2026-05-10', '2026-05-11', '2026-05-20'));
        $this->createSyarat($j1, $syarat);
        $this->createBiaya($j1, 'Biaya Pendaftaran PSB Reguler', 75000);
        $this->createFormulir($j1, 'Formulir Data Tambahan MI', $fields);
        $this->createKuotaJurusan($j1, ['MINU-SAM-REG' => 25]);

        // Jalur 2: Beasiswa Tahfidz
        $j2 = $this->createJalur($pembukaanId, ['kode' => 'TAHFIDZ', 'nama' => 'Beasiswa Tahfidz Al-Quran', 'kuota' => 5, 'deskripsi' => 'Bebas biaya untuk calon santri penghafal Al-Quran min. 1 juz', 'urutan' => 2]);
        $this->createJadwal($j2, $this->jadwalLewat('2026-03-01', '2026-04-30', '2026-05-01', '2026-05-05', '2026-05-06', '2026-05-10', '2026-05-11', '2026-05-20'));
        $this->createSyarat($j2, array_merge($syarat, [
            ['nama' => 'Sertifikat/Surat Keterangan Hafalan', 'tipe' => 'dokumen', 'wajib' => true, 'keterangan' => 'Dikeluarkan oleh TPQ/Pesantren', 'urutan' => 5],
        ]));
        $this->createBiaya($j2, 'Beasiswa Tahfidz (Gratis)', 0, 'Seluruh biaya ditanggung lembaga untuk jalur tahfidz');
        $this->createFormulir($j2, 'Formulir Data Tambahan MI', $fields);
        $this->createKuotaJurusan($j2, ['MINU-SAM-REG' => 5]);

        $this->command->info('  ✓ MINU Sunan Ampel Maron: PSB 2026/2027 (tutup) — 2 jalur');
    }

    private function seedMtsNuSunanAmpelMaron(): void
    {
        $lembagaId = $this->getLembagaId('MTSNU-SAM');

        $pembukaanId = $this->createPembukaan($lembagaId, [
            'nama'        => 'Penerimaan Santri Baru (PSB) 2026/2027',
            'deskripsi'   => 'MTs NU Sunan Ampel Maron membuka penerimaan santri baru TP 2026/2027 melalui 3 jalur: Reguler, Prestasi, dan Tahfidz Al-Quran.',
            'mulai'       => '2026-04-01',
            'selesai'     => '2026-06-30',
            'status'      => 'buka',
        ]);

        $syarat = $this->syaratMTs();
        $fields = $this->formulirMTs();

        // Jalur 1: Reguler (60 kuota → 20/jurusan × 3 jurusan)
        $j1 = $this->createJalur($pembukaanId, ['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'kuota' => 60, 'urutan' => 1]);
        $this->createJadwal($j1, $this->jadwalAktif('2026-04-01', '2026-06-30', '2026-07-01', '2026-07-07', '2026-07-08', '2026-07-10', '2026-07-11', '2026-07-20'));
        $this->createSyarat($j1, $syarat);
        $this->createBiaya($j1, 'Biaya Pendaftaran Jalur Reguler', 125000);
        $this->createFormulir($j1, 'Formulir Pendaftaran MTs', $fields);
        $this->createKuotaJurusan($j1, ['MTSNU-SAM-REG' => 20, 'MTSNU-SAM-UNG' => 20, 'MTSNU-SAM-THF' => 20]);

        // Jalur 2: Prestasi (30 kuota → 10/jurusan × 3 jurusan)
        $j2 = $this->createJalur($pembukaanId, ['kode' => 'PRESTASI', 'nama' => 'Jalur Prestasi', 'kuota' => 30, 'deskripsi' => 'Jalur khusus peserta dengan prestasi akademik atau non-akademik tingkat kabupaten ke atas', 'urutan' => 2]);
        $this->createJadwal($j2, $this->jadwalAktif('2026-04-01', '2026-06-30', '2026-07-01', '2026-07-07', '2026-07-08', '2026-07-10', '2026-07-11', '2026-07-20'));
        $this->createSyarat($j2, array_merge($syarat, [
            ['nama' => 'Sertifikat/Piagam Prestasi', 'tipe' => 'dokumen', 'wajib' => true, 'keterangan' => 'Minimal tingkat kabupaten/kota, 3 tahun terakhir', 'urutan' => 5],
        ]));
        $this->createBiaya($j2, 'Biaya Pendaftaran Jalur Prestasi', 125000);
        $this->createFormulir($j2, 'Formulir Pendaftaran MTs Prestasi', array_merge($fields, [
            ['kode' => 'jenis_prestasi',    'label' => 'Jenis Prestasi',    'tipe' => 'select',   'required' => true,  'urutan' => 4, 'opsi' => [['value' => 'akademik', 'label' => 'Akademik'], ['value' => 'non_akademik', 'label' => 'Non-Akademik'], ['value' => 'keagamaan', 'label' => 'Keagamaan']]],
            ['kode' => 'tingkat_prestasi',  'label' => 'Tingkat Prestasi',  'tipe' => 'select',   'required' => true,  'urutan' => 5, 'opsi' => [['value' => 'internasional', 'label' => 'Internasional'], ['value' => 'nasional', 'label' => 'Nasional'], ['value' => 'provinsi', 'label' => 'Provinsi'], ['value' => 'kabupaten', 'label' => 'Kabupaten/Kota']]],
        ]));
        $this->createKuotaJurusan($j2, ['MTSNU-SAM-REG' => 10, 'MTSNU-SAM-UNG' => 10, 'MTSNU-SAM-THF' => 10]);

        // Jalur 3: Tahfidz (15 kuota → khusus kelas tahfidz)
        $j3 = $this->createJalur($pembukaanId, ['kode' => 'TAHFIDZ', 'nama' => 'Jalur Khusus Tahfidz', 'kuota' => 15, 'deskripsi' => 'Jalur khusus hafidz/hafidzah minimal Juz 30 beserta bacaan tartil', 'urutan' => 3]);
        $this->createJadwal($j3, $this->jadwalAktif('2026-04-01', '2026-06-30', '2026-07-01', '2026-07-07', '2026-07-08', '2026-07-10', '2026-07-11', '2026-07-20'));
        $this->createSyarat($j3, array_merge($syarat, [
            ['nama' => 'Surat Keterangan Hafalan Juz 30', 'tipe' => 'dokumen', 'wajib' => true, 'keterangan' => 'Dikeluarkan oleh ustadz/guru ngaji yang diketahui kepala TPQ/Pesantren', 'urutan' => 5],
            ['nama' => 'Surat Rekomendasi Pesantren/TPQ',  'tipe' => 'dokumen', 'wajib' => false, 'keterangan' => 'Diutamakan yang pernah mondok/mengikuti TPQ', 'urutan' => 6],
        ]));
        $this->createBiaya($j3, 'Biaya Pendaftaran Jalur Tahfidz', 100000);
        $this->createFormulir($j3, 'Formulir Pendaftaran MTs Tahfidz', array_merge($fields, [
            ['kode' => 'jumlah_hafalan',  'label' => 'Jumlah Hafalan (Juz)', 'tipe' => 'number',  'required' => true,  'urutan' => 4, 'opsi' => null],
            ['kode' => 'nama_pengajar',   'label' => 'Nama Ustadz/Pengajar', 'tipe' => 'text',    'required' => true,  'urutan' => 5, 'opsi' => null],
        ]));
        $this->createKuotaJurusan($j3, ['MTSNU-SAM-THF' => 15]);

        $this->command->info('  ✓ MTs NU Sunan Ampel Maron: PSB 2026/2027 (buka) — 3 jalur');
    }

    private function seedMtsNuMaron(): void
    {
        $lembagaId = $this->getLembagaId('MTSNU-MRN');

        $pembukaanId = $this->createPembukaan($lembagaId, [
            'nama'        => 'Penerimaan Santri Baru (PSB) 2026/2027',
            'deskripsi'   => 'MTs NU Maron membuka penerimaan santri baru TP 2026/2027 melalui jalur Reguler, Prestasi, dan Beasiswa.',
            'mulai'       => '2026-04-15',
            'selesai'     => '2026-06-30',
            'status'      => 'buka',
        ]);

        $syarat = $this->syaratMTs();
        $fields = $this->formulirMTs();

        // Jalur 1: Reguler (50 kuota → 25/jurusan × 2 jurusan)
        $j1 = $this->createJalur($pembukaanId, ['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'kuota' => 50, 'urutan' => 1]);
        $this->createJadwal($j1, $this->jadwalAktif('2026-04-15', '2026-06-30', '2026-07-01', '2026-07-07', '2026-07-08', '2026-07-10', '2026-07-11', '2026-07-20'));
        $this->createSyarat($j1, $syarat);
        $this->createBiaya($j1, 'Biaya Pendaftaran Jalur Reguler', 125000);
        $this->createFormulir($j1, 'Formulir Pendaftaran MTs', $fields);
        $this->createKuotaJurusan($j1, ['MTSNU-MRN-REG' => 25, 'MTSNU-MRN-UNG' => 25]);

        // Jalur 2: Prestasi (25 kuota → 13 reguler, 12 unggulan)
        $j2 = $this->createJalur($pembukaanId, ['kode' => 'PRESTASI', 'nama' => 'Jalur Prestasi', 'kuota' => 25, 'deskripsi' => 'Untuk peserta dengan nilai rata-rata rapor kelas 6 minimal 80 atau prestasi lomba', 'urutan' => 2]);
        $this->createJadwal($j2, $this->jadwalAktif('2026-04-15', '2026-06-30', '2026-07-01', '2026-07-07', '2026-07-08', '2026-07-10', '2026-07-11', '2026-07-20'));
        $this->createSyarat($j2, array_merge($syarat, [
            ['nama' => 'Piagam/Sertifikat Prestasi', 'tipe' => 'dokumen', 'wajib' => false, 'keterangan' => 'Bagi yang memiliki prestasi lomba akademik/non-akademik', 'urutan' => 5],
        ]));
        $this->createBiaya($j2, 'Biaya Pendaftaran Jalur Prestasi', 125000);
        $this->createFormulir($j2, 'Formulir Pendaftaran MTs Prestasi', array_merge($fields, [
            ['kode' => 'nilai_rata_rapor', 'label' => 'Nilai Rata-rata Rapor Kelas 6', 'tipe' => 'number', 'required' => true, 'urutan' => 4, 'opsi' => null],
        ]));
        $this->createKuotaJurusan($j2, ['MTSNU-MRN-REG' => 13, 'MTSNU-MRN-UNG' => 12]);

        // Jalur 3: Beasiswa/Afirmasi (10 kuota)
        $j3 = $this->createJalur($pembukaanId, ['kode' => 'BEASISWA', 'nama' => 'Beasiswa / Afirmasi', 'kuota' => 10, 'deskripsi' => 'Jalur bebas biaya untuk calon santri dari keluarga kurang mampu atau yatim/piatu', 'urutan' => 3]);
        $this->createJadwal($j3, $this->jadwalAktif('2026-04-15', '2026-06-30', '2026-07-01', '2026-07-07', '2026-07-08', '2026-07-10', '2026-07-11', '2026-07-20'));
        $this->createSyarat($j3, array_merge($syarat, [
            ['nama' => 'Surat Keterangan Tidak Mampu (SKTM)', 'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Dikeluarkan oleh Kepala Desa/Kelurahan setempat', 'urutan' => 5],
            ['nama' => 'Kartu PKH / KKS / KIP',               'tipe' => 'dokumen', 'wajib' => false, 'keterangan' => 'Salah satu, bila memiliki', 'urutan' => 6],
        ]));
        $this->createBiaya($j3, 'Beasiswa Afirmasi (Gratis)', 0, 'Bebas biaya — disubsidi sepenuhnya oleh lembaga');
        $this->createFormulir($j3, 'Formulir Pendaftaran MTs Beasiswa', array_merge($fields, [
            ['kode' => 'alasan_beasiswa', 'label' => 'Alasan Mengajukan Beasiswa', 'tipe' => 'textarea', 'required' => true, 'urutan' => 4, 'opsi' => null],
            ['kode' => 'pekerjaan_ortu',  'label' => 'Pekerjaan Orang Tua',        'tipe' => 'text',     'required' => true, 'urutan' => 5, 'opsi' => null],
        ]));
        $this->createKuotaJurusan($j3, ['MTSNU-MRN-REG' => 6, 'MTSNU-MRN-UNG' => 4]);

        $this->command->info('  ✓ MTs NU Maron: PSB 2026/2027 (buka) — 3 jalur');
    }

    private function seedSmpMaarifKraksaan(): void
    {
        $lembagaId = $this->getLembagaId('SMP-KRK');

        $pembukaanId = $this->createPembukaan($lembagaId, [
            'nama'        => "PPDB SMP Ma'arif Kraksaan 2026/2027",
            'deskripsi'   => "SMP Ma'arif Kraksaan membuka PPDB TP 2026/2027 melalui Jalur Zonasi, Reguler, dan Prestasi.",
            'mulai'       => '2026-05-01',
            'selesai'     => '2026-07-15',
            'status'      => 'buka',
        ]);

        $syarat = $this->syaratMTs(); // SMP pakai syarat yang sama dengan MTs
        $fields = $this->formulirMTs();

        // Jalur 1: Zonasi (30 kuota → 15/jurusan × 2 jurusan)
        $j1 = $this->createJalur($pembukaanId, ['kode' => 'ZONASI', 'nama' => 'Jalur Zonasi', 'kuota' => 30, 'deskripsi' => 'Diprioritaskan bagi calon peserta yang berdomisili dalam radius 3 km dari sekolah', 'urutan' => 1]);
        $this->createJadwal($j1, $this->jadwalAktif('2026-05-01', '2026-06-30', '2026-07-01', '2026-07-05', '2026-07-06', '2026-07-08', '2026-07-09', '2026-07-15'));
        $this->createSyarat($j1, array_merge($syarat, [
            ['nama' => 'Surat Keterangan Domisili / KTP Orang Tua', 'tipe' => 'dokumen', 'wajib' => true, 'keterangan' => 'Domisili dalam radius 3 km dari sekolah', 'urutan' => 5],
        ]));
        $this->createBiaya($j1, 'Biaya Pendaftaran Jalur Zonasi', 100000);
        $this->createFormulir($j1, 'Formulir Pendaftaran SMP', $fields);
        $this->createKuotaJurusan($j1, ['SMP-KRK-REG' => 15, 'SMP-KRK-OLY' => 15]);

        // Jalur 2: Reguler (50 kuota → 25/jurusan × 2 jurusan)
        $j2 = $this->createJalur($pembukaanId, ['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'kuota' => 50, 'urutan' => 2]);
        $this->createJadwal($j2, $this->jadwalAktif('2026-05-01', '2026-06-30', '2026-07-01', '2026-07-05', '2026-07-06', '2026-07-08', '2026-07-09', '2026-07-15'));
        $this->createSyarat($j2, $syarat);
        $this->createBiaya($j2, 'Biaya Pendaftaran Jalur Reguler', 100000);
        $this->createFormulir($j2, 'Formulir Pendaftaran SMP', $fields);
        $this->createKuotaJurusan($j2, ['SMP-KRK-REG' => 25, 'SMP-KRK-OLY' => 25]);

        // Jalur 3: Prestasi (20 kuota → 10/jurusan × 2 jurusan)
        $j3 = $this->createJalur($pembukaanId, ['kode' => 'PRESTASI', 'nama' => 'Jalur Prestasi', 'kuota' => 20, 'deskripsi' => 'Untuk peserta berprestasi lomba akademik/non-akademik atau nilai rapor di atas rata-rata', 'urutan' => 3]);
        $this->createJadwal($j3, $this->jadwalAktif('2026-05-01', '2026-06-30', '2026-07-01', '2026-07-05', '2026-07-06', '2026-07-08', '2026-07-09', '2026-07-15'));
        $this->createSyarat($j3, array_merge($syarat, [
            ['nama' => 'Piagam/Sertifikat Prestasi', 'tipe' => 'dokumen', 'wajib' => true, 'keterangan' => 'Minimal tingkat kecamatan, 3 tahun terakhir', 'urutan' => 5],
        ]));
        $this->createBiaya($j3, 'Biaya Pendaftaran Jalur Prestasi', 75000);
        $this->createFormulir($j3, 'Formulir Pendaftaran SMP Prestasi', array_merge($fields, [
            ['kode' => 'jenis_prestasi',   'label' => 'Jenis Prestasi',   'tipe' => 'select',   'required' => true, 'urutan' => 4, 'opsi' => [['value' => 'akademik', 'label' => 'Akademik'], ['value' => 'olahraga', 'label' => 'Olahraga'], ['value' => 'seni', 'label' => 'Seni & Budaya'], ['value' => 'keagamaan', 'label' => 'Keagamaan']]],
            ['kode' => 'tingkat_prestasi', 'label' => 'Tingkat Prestasi', 'tipe' => 'select',   'required' => true, 'urutan' => 5, 'opsi' => [['value' => 'internasional', 'label' => 'Internasional'], ['value' => 'nasional', 'label' => 'Nasional'], ['value' => 'provinsi', 'label' => 'Provinsi'], ['value' => 'kabupaten', 'label' => 'Kabupaten/Kota'], ['value' => 'kecamatan', 'label' => 'Kecamatan']]],
        ]));
        $this->createKuotaJurusan($j3, ['SMP-KRK-REG' => 10, 'SMP-KRK-OLY' => 10]);

        $this->command->info("  ✓ SMP Ma'arif Kraksaan: PPDB 2026/2027 (buka) — 3 jalur");
    }

    private function seedManuMaron(): void
    {
        $lembagaId = $this->getLembagaId('MANU-MRN');

        $pembukaanId = $this->createPembukaan($lembagaId, [
            'nama'        => 'PPDB MANU Maron 2026/2027',
            'deskripsi'   => 'Madrasah Aliyah NU Maron membuka Penerimaan Peserta Didik Baru TP 2026/2027 melalui 4 jalur: Reguler, Prestasi, Tahfidz, dan Afirmasi.',
            'mulai'       => '2026-05-01',
            'selesai'     => '2026-08-31',
            'status'      => 'buka',
        ]);

        $syarat = $this->syaratMA();
        $fields = $this->formulirMA();

        // Jalur 1: Reguler (60 kuota → 15/jurusan × 4 jurusan)
        $j1 = $this->createJalur($pembukaanId, ['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'kuota' => 60, 'urutan' => 1]);
        $this->createJadwal($j1, $this->jadwalAktif('2026-05-01', '2026-07-31', '2026-08-01', '2026-08-07', '2026-08-08', '2026-08-10', '2026-08-11', '2026-08-20'));
        $this->createSyarat($j1, $syarat);
        $this->createBiaya($j1, 'Biaya Pendaftaran Jalur Reguler', 150000);
        $this->createFormulir($j1, 'Formulir Pendaftaran MA', $fields);
        $this->createKuotaJurusan($j1, ['MANU-IPA' => 15, 'MANU-IPS' => 15, 'MANU-BHS' => 15, 'MANU-AG' => 15]);

        // Jalur 2: Prestasi (40 kuota → 10/jurusan × 4 jurusan)
        $j2 = $this->createJalur($pembukaanId, ['kode' => 'PRESTASI', 'nama' => 'Jalur Prestasi', 'kuota' => 40, 'deskripsi' => 'Untuk peserta berprestasi akademik/non-akademik tingkat kabupaten ke atas atau nilai rata-rata MTs/SMP ≥ 85', 'urutan' => 2]);
        $this->createJadwal($j2, $this->jadwalAktif('2026-05-01', '2026-07-31', '2026-08-01', '2026-08-07', '2026-08-08', '2026-08-10', '2026-08-11', '2026-08-20'));
        $this->createSyarat($j2, array_merge($syarat, [
            ['nama' => 'Piagam/Sertifikat Prestasi', 'tipe' => 'dokumen', 'wajib' => false, 'keterangan' => 'Minimal tingkat kabupaten/kota — akademik, olahraga, atau keagamaan', 'urutan' => 6],
        ]));
        $this->createBiaya($j2, 'Biaya Pendaftaran Jalur Prestasi', 150000);
        $this->createFormulir($j2, 'Formulir Pendaftaran MA Prestasi', array_merge($fields, [
            ['kode' => 'nilai_rata_mts',   'label' => 'Nilai Rata-rata MTs/SMP', 'tipe' => 'number', 'required' => true,  'urutan' => 5, 'opsi' => null],
            ['kode' => 'jenis_prestasi',   'label' => 'Jenis Prestasi Unggulan', 'tipe' => 'select', 'required' => false, 'urutan' => 6, 'opsi' => [['value' => 'olimpiade', 'label' => 'Olimpiade Sains'], ['value' => 'olahraga', 'label' => 'Olahraga'], ['value' => 'seni', 'label' => 'Seni & Budaya'], ['value' => 'keagamaan', 'label' => 'MTQ/Keagamaan'], ['value' => 'lainnya', 'label' => 'Lainnya']]],
        ]));
        $this->createKuotaJurusan($j2, ['MANU-IPA' => 10, 'MANU-IPS' => 10, 'MANU-BHS' => 10, 'MANU-AG' => 10]);

        // Jalur 3: Tahfidz (20 kuota → 5/jurusan × 4 jurusan)
        $j3 = $this->createJalur($pembukaanId, ['kode' => 'TAHFIDZ', 'nama' => 'Jalur Tahfidz Al-Quran', 'kuota' => 20, 'deskripsi' => 'Jalur khusus hafidz/hafidzah minimal Juz 30 — mendapatkan keringanan SPP 50%', 'urutan' => 3]);
        $this->createJadwal($j3, $this->jadwalAktif('2026-05-01', '2026-07-31', '2026-08-01', '2026-08-07', '2026-08-08', '2026-08-10', '2026-08-11', '2026-08-20'));
        $this->createSyarat($j3, array_merge($syarat, [
            ['nama' => 'Surat Keterangan Hafalan Quran', 'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Dikeluarkan oleh pimpinan pesantren/lembaga tahfidz yang bersangkutan', 'urutan' => 6],
            ['nama' => 'Surat Rekomendasi Pesantren',    'tipe' => 'dokumen', 'wajib' => false, 'keterangan' => 'Diutamakan yang pernah mondok', 'urutan' => 7],
        ]));
        $this->createBiaya($j3, 'Biaya Pendaftaran Jalur Tahfidz', 125000, 'Keringanan 50% SPP selama masa studi');
        $this->createFormulir($j3, 'Formulir Pendaftaran MA Tahfidz', array_merge($fields, [
            ['kode' => 'jumlah_hafalan',   'label' => 'Jumlah Hafalan Saat Ini (Juz)', 'tipe' => 'number', 'required' => true, 'urutan' => 5, 'opsi' => null],
            ['kode' => 'nama_pesantren',   'label' => 'Nama Pesantren/Lembaga Tahfidz', 'tipe' => 'text',  'required' => true, 'urutan' => 6, 'opsi' => null],
            ['kode' => 'tahun_mulai_hfz',  'label' => 'Tahun Mulai Menghafal',          'tipe' => 'text',  'required' => true, 'urutan' => 7, 'opsi' => null],
        ]));
        $this->createKuotaJurusan($j3, ['MANU-IPA' => 5, 'MANU-IPS' => 5, 'MANU-BHS' => 5, 'MANU-AG' => 5]);

        // Jalur 4: Afirmasi/Beasiswa (15 kuota → distribusi per jurusan)
        $j4 = $this->createJalur($pembukaanId, ['kode' => 'AFIRMASI', 'nama' => 'Jalur Afirmasi / Beasiswa', 'kuota' => 15, 'deskripsi' => 'Jalur bebas biaya bagi peserta dari keluarga yatim/piatu, kurang mampu, atau penerima PKH/KIP', 'urutan' => 4]);
        $this->createJadwal($j4, $this->jadwalAktif('2026-05-01', '2026-07-31', '2026-08-01', '2026-08-07', '2026-08-08', '2026-08-10', '2026-08-11', '2026-08-20'));
        $this->createSyarat($j4, array_merge($syarat, [
            ['nama' => 'Surat Keterangan Tidak Mampu (SKTM)', 'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Dari Kepala Desa/Kelurahan — asli, bukan fotokopi', 'urutan' => 6],
            ['nama' => 'Kartu PKH / KKS / KIP',               'tipe' => 'dokumen', 'wajib' => false, 'keterangan' => 'Salah satu, bila memiliki', 'urutan' => 7],
        ]));
        $this->createBiaya($j4, 'Beasiswa Afirmasi (Gratis)', 0, 'Seluruh biaya pendaftaran dan SPP ditanggung lembaga');
        $this->createFormulir($j4, 'Formulir Pendaftaran MA Afirmasi', array_merge($fields, [
            ['kode' => 'alasan_beasiswa',  'label' => 'Alasan Mengajukan Beasiswa',    'tipe' => 'textarea', 'required' => true, 'urutan' => 5, 'opsi' => null],
            ['kode' => 'penghasilan_ortu', 'label' => 'Penghasilan Orang Tua/Bulan',   'tipe' => 'text',     'required' => true, 'urutan' => 6, 'opsi' => null],
        ]));
        $this->createKuotaJurusan($j4, ['MANU-IPA' => 4, 'MANU-IPS' => 4, 'MANU-BHS' => 4, 'MANU-AG' => 3]);

        $this->command->info('  ✓ MANU Maron: PPDB 2026/2027 (buka) — 4 jalur');
    }

    // =========================================================================
    // TEMPLATE SYARAT PER JENJANG
    // =========================================================================

    private function syaratMI(): array
    {
        return [
            ['nama' => 'Kartu Keluarga (KK)',           'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Fotokopi KK yang masih berlaku',                  'urutan' => 1],
            ['nama' => 'Akta Kelahiran',                 'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Fotokopi akta kelahiran anak',                    'urutan' => 2],
            ['nama' => 'Ijazah / STTB TK/RA',           'tipe' => 'dokumen', 'wajib' => false, 'keterangan' => 'Bagi yang sudah lulus TK/RA',                     'urutan' => 3],
            ['nama' => 'Surat Keterangan Sehat',         'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Dari dokter atau puskesmas setempat',             'urutan' => 4],
        ];
    }

    private function syaratMTs(): array
    {
        return [
            ['nama' => 'Kartu Keluarga (KK)',            'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Fotokopi KK yang masih berlaku',                  'urutan' => 1],
            ['nama' => 'Akta Kelahiran',                  'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Fotokopi akta kelahiran',                          'urutan' => 2],
            ['nama' => 'Ijazah / SKL SD/MI',              'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Fotokopi ijazah atau Surat Keterangan Lulus',      'urutan' => 3],
            ['nama' => 'Rapor Kelas 4, 5, 6',            'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Fotokopi rapor 3 semester terakhir (sem. genap)', 'urutan' => 4],
        ];
    }

    private function syaratMA(): array
    {
        return [
            ['nama' => 'Kartu Keluarga (KK)',             'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Fotokopi KK yang masih berlaku',                  'urutan' => 1],
            ['nama' => 'Akta Kelahiran',                   'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Fotokopi akta kelahiran',                          'urutan' => 2],
            ['nama' => 'Ijazah / SKL MTs/SMP',             'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Fotokopi ijazah atau Surat Keterangan Lulus',      'urutan' => 3],
            ['nama' => 'Rapor MTs/SMP Semester 1–5',      'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => 'Fotokopi rapor 5 semester terakhir',              'urutan' => 4],
            ['nama' => 'Pas Foto 3×4 Berwarna',           'tipe' => 'dokumen', 'wajib' => true,  'keterangan' => '6 lembar, background merah, baju putih',          'urutan' => 5],
        ];
    }

    // =========================================================================
    // TEMPLATE FORMULIR PER JENJANG
    // =========================================================================

    private function formulirMI(): array
    {
        return [
            ['kode' => 'asal_tk_ra',        'label' => 'Asal TK/RA (jika ada)',    'tipe' => 'text',   'required' => false, 'urutan' => 1, 'opsi' => null],
            ['kode' => 'kemampuan_baca',     'label' => 'Kemampuan Membaca',        'tipe' => 'select', 'required' => true,  'urutan' => 2, 'opsi' => [['value' => 'lancar', 'label' => 'Lancar'], ['value' => 'sedang', 'label' => 'Sedang'], ['value' => 'belum_bisa', 'label' => 'Belum Bisa']]],
            ['kode' => 'hafalan_juz_amma',   'label' => 'Hafalan Juz Amma',        'tipe' => 'select', 'required' => true,  'urutan' => 3, 'opsi' => [['value' => 'hafalqur', 'label' => 'Sudah Hafal Juz Amma'], ['value' => 'lebih_10', 'label' => '> 10 Surat'], ['value' => '1_10_surat', 'label' => '1–10 Surat'], ['value' => 'belum', 'label' => 'Belum Ada Hafalan']]],
        ];
    }

    private function formulirMTs(): array
    {
        return [
            ['kode' => 'asal_sekolah',       'label' => 'Asal SD/MI',              'tipe' => 'text',   'required' => true,  'urutan' => 1, 'opsi' => null],
            ['kode' => 'nilai_rata_rata',     'label' => 'Nilai Rata-rata Kelas 6', 'tipe' => 'number', 'required' => true,  'urutan' => 2, 'opsi' => null],
            ['kode' => 'hobi',               'label' => 'Hobi / Minat',            'tipe' => 'text',   'required' => false, 'urutan' => 3, 'opsi' => null],
        ];
    }

    private function formulirMA(): array
    {
        return [
            ['kode' => 'asal_sekolah',       'label' => 'Asal MTs/SMP',            'tipe' => 'text',   'required' => true,  'urutan' => 1, 'opsi' => null],
            ['kode' => 'pilihan_jurusan',     'label' => 'Pilihan Program Studi',   'tipe' => 'select', 'required' => true,  'urutan' => 2, 'opsi' => [['value' => 'IPA', 'label' => 'IPA (Ilmu Pengetahuan Alam)'], ['value' => 'IPS', 'label' => 'IPS (Ilmu Pengetahuan Sosial)'], ['value' => 'BHS', 'label' => 'Bahasa Arab'], ['value' => 'AG', 'label' => 'Keagamaan']]],
            ['kode' => 'motivasi',           'label' => 'Motivasi Masuk MANU Maron', 'tipe' => 'textarea', 'required' => false, 'urutan' => 3, 'opsi' => null],
            ['kode' => 'rencana_kuliah',     'label' => 'Rencana Studi Lanjut',    'tipe' => 'select', 'required' => false, 'urutan' => 4, 'opsi' => [['value' => 'perguruan_tinggi', 'label' => 'Perguruan Tinggi'], ['value' => 'pesantren', 'label' => 'Pesantren/Takhassus'], ['value' => 'kerja', 'label' => 'Langsung Bekerja'], ['value' => 'belum_tahu', 'label' => 'Belum Tahu']]],
        ];
    }

    // =========================================================================
    // TEMPLATE JADWAL
    // =========================================================================

    private function jadwalLewat(
        string $daftarMulai, string $daftarSelesai,
        string $seleksiMulai, string $seleksiSelesai,
        string $umumMulai,   string $umumSelesai,
        string $daftarUlangMulai, string $daftarUlangSelesai
    ): array {
        return [
            ['tipe' => 'pendaftaran', 'nama' => 'Pendaftaran Online',         'mulai' => $daftarMulai,    'selesai' => $daftarSelesai,    'status' => 'nonaktif'],
            ['tipe' => 'seleksi',     'nama' => 'Verifikasi & Seleksi Data',  'mulai' => $seleksiMulai,   'selesai' => $seleksiSelesai,   'status' => 'nonaktif'],
            ['tipe' => 'pengumuman',  'nama' => 'Pengumuman Hasil Seleksi',   'mulai' => $umumMulai,      'selesai' => $umumSelesai,      'status' => 'nonaktif'],
            ['tipe' => 'daftar_ulang','nama' => 'Daftar Ulang & Pembayaran',  'mulai' => $daftarUlangMulai, 'selesai' => $daftarUlangSelesai, 'status' => 'nonaktif'],
        ];
    }

    private function jadwalAktif(
        string $daftarMulai, string $daftarSelesai,
        string $seleksiMulai, string $seleksiSelesai,
        string $umumMulai,   string $umumSelesai,
        string $daftarUlangMulai, string $daftarUlangSelesai
    ): array {
        return [
            ['tipe' => 'pendaftaran', 'nama' => 'Pendaftaran Online',         'mulai' => $daftarMulai,    'selesai' => $daftarSelesai,    'status' => 'aktif'],
            ['tipe' => 'seleksi',     'nama' => 'Verifikasi & Seleksi Data',  'mulai' => $seleksiMulai,   'selesai' => $seleksiSelesai,   'status' => 'aktif'],
            ['tipe' => 'pengumuman',  'nama' => 'Pengumuman Hasil Seleksi',   'mulai' => $umumMulai,      'selesai' => $umumSelesai,      'status' => 'aktif'],
            ['tipe' => 'daftar_ulang','nama' => 'Daftar Ulang & Pembayaran',  'mulai' => $daftarUlangMulai, 'selesai' => $daftarUlangSelesai, 'status' => 'aktif'],
        ];
    }

    // =========================================================================
    // BUILDER HELPERS
    // =========================================================================

    private function getLembagaId(string $kode): int
    {
        $id = DB::table('lembaga')->where('kode', $kode)->value('id');

        if (! $id) {
            $this->command->error("Lembaga [{$kode}] tidak ditemukan. Pastikan LembagaSeeder sudah dijalankan.");
            throw new \RuntimeException("Lembaga [{$kode}] tidak ditemukan.");
        }

        return $id;
    }

    private function createPembukaan(int $lembagaId, array $data): int
    {
        $existing = DB::table('pembukaan_ppdb')
            ->where('lembaga_id', $lembagaId)
            ->where('tahun_pelajaran_id', $this->tahunId)
            ->value('id');

        if ($existing) {
            return $existing;
        }

        return DB::table('pembukaan_ppdb')->insertGetId(array_merge([
            'lembaga_id'         => $lembagaId,
            'tahun_pelajaran_id' => $this->tahunId,
            'deskripsi'          => null,
            'status'             => 'buka',
        ], $data, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    private function createJalur(int $pembukaanId, array $data): int
    {
        $existing = DB::table('jalur_pendaftaran')
            ->where('pembukaan_ppdb_id', $pembukaanId)
            ->where('kode_jalur', $data['kode'])
            ->value('id');

        if ($existing) {
            return $existing;
        }

        return DB::table('jalur_pendaftaran')->insertGetId([
            'pembukaan_ppdb_id' => $pembukaanId,
            'kode_jalur'        => $data['kode'],
            'nama'              => $data['nama'],
            'deskripsi'         => $data['deskripsi'] ?? null,
            'kuota'             => $data['kuota'],
            'urutan'            => $data['urutan'],
            'status'            => 'aktif',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    private function createJadwal(int $jalurId, array $jadwalList): void
    {
        foreach ($jadwalList as $jadwal) {
            DB::table('jadwal_pendaftaran')->updateOrInsert(
                ['jalur_pendaftaran_id' => $jalurId, 'tipe' => $jadwal['tipe']],
                [
                    'nama'       => $jadwal['nama'],
                    'mulai'      => $jadwal['mulai'],
                    'selesai'    => $jadwal['selesai'],
                    'status'     => $jadwal['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function createSyarat(int $jalurId, array $syaratList): void
    {
        foreach ($syaratList as $s) {
            $exists = DB::table('syarat_pendaftaran')
                ->where('jalur_pendaftaran_id', $jalurId)
                ->where('nama', $s['nama'])
                ->exists();

            if (! $exists) {
                DB::table('syarat_pendaftaran')->insert([
                    'jalur_pendaftaran_id' => $jalurId,
                    'tahun_pelajaran_id'   => $this->tahunId,
                    'nama'                 => $s['nama'],
                    'tipe'                 => $s['tipe'],
                    'wajib'                => $s['wajib'],
                    'keterangan'           => $s['keterangan'],
                    'urutan'               => $s['urutan'],
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }
        }
    }

    private function createBiaya(int $jalurId, string $nama, int $nominal, string $deskripsi = 'Biaya administrasi pendaftaran'): void
    {
        DB::table('biaya_registrasi')->updateOrInsert(
            ['jalur_pendaftaran_id' => $jalurId, 'tahun_pelajaran_id' => $this->tahunId],
            [
                'nama'       => $nama,
                'nominal'    => $nominal,
                'deskripsi'  => $nominal === 0 ? 'Gratis — ditanggung oleh lembaga' : $deskripsi,
                'is_aktif'   => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function createFormulir(int $jalurId, string $nama, array $fields): void
    {
        $formId = DB::table('formulir_pendaftaran')
            ->where('jalur_pendaftaran_id', $jalurId)
            ->where('tahun_pelajaran_id', $this->tahunId)
            ->value('id');

        if (! $formId) {
            $formId = DB::table('formulir_pendaftaran')->insertGetId([
                'jalur_pendaftaran_id' => $jalurId,
                'tahun_pelajaran_id'   => $this->tahunId,
                'nama'                 => $nama,
                'is_aktif'             => true,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            foreach ($fields as $f) {
                DB::table('formulir_field')->insert([
                    'formulir_pendaftaran_id' => $formId,
                    'kode_field'              => $f['kode'],
                    'label'                   => $f['label'],
                    'tipe_field'              => $f['tipe'],
                    'is_required'             => $f['required'],
                    'is_statis'               => false,
                    'urutan'                  => $f['urutan'],
                    'opsi'                    => isset($f['opsi']) ? json_encode($f['opsi']) : null,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ]);
            }
        }
    }

    /** @param array<string,int> $kuotaMap  [ jurusan_kode => kuota ] */
    private function createKuotaJurusan(int $jalurId, array $kuotaMap): void
    {
        foreach ($kuotaMap as $jurusanKode => $kuota) {
            $jurusanId = DB::table('jurusan')->where('kode', $jurusanKode)->value('id');

            if (! $jurusanId) {
                $this->command->warn("    ⚠ Jurusan [{$jurusanKode}] tidak ditemukan, lewati kuota.");
                continue;
            }

            DB::table('kuota_jurusan')->updateOrInsert(
                [
                    'tahun_pelajaran_id'   => $this->tahunId,
                    'jalur_pendaftaran_id' => $jalurId,
                    'jurusan_id'           => $jurusanId,
                ],
                [
                    'kuota'      => $kuota,
                    'terisi'     => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
