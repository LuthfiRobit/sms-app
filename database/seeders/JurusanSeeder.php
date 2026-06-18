<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        $jurusanPerLembaga = [
            // ─── MI (Elementary) — tidak ada jurusan formal, 1 program reguler ───
            'MINU-KRK' => [
                ['kode' => 'MINU-KRK-REG', 'nama' => 'Kelas Reguler', 'deskripsi' => 'Program kelas reguler MI', 'urutan' => 1],
            ],
            'MINU-SAM' => [
                ['kode' => 'MINU-SAM-REG', 'nama' => 'Kelas Reguler', 'deskripsi' => 'Program kelas reguler MI', 'urutan' => 1],
            ],

            // ─── MTs (Islamic Junior High) — kelas berdasarkan program ───
            'MTSNU-SAM' => [
                ['kode' => 'MTSNU-SAM-REG', 'nama' => 'Kelas Reguler', 'deskripsi' => 'Program pembelajaran umum dan agama', 'urutan' => 1],
                ['kode' => 'MTSNU-SAM-UNG', 'nama' => 'Kelas Unggulan', 'deskripsi' => 'Program akselerasi kurikulum plus hafalan', 'urutan' => 2],
                ['kode' => 'MTSNU-SAM-THF', 'nama' => 'Kelas Tahfidz', 'deskripsi' => 'Program hafalan Al-Quran 30 juz', 'urutan' => 3],
            ],
            'MTSNU-MRN' => [
                ['kode' => 'MTSNU-MRN-REG', 'nama' => 'Kelas Reguler', 'deskripsi' => 'Program pembelajaran umum dan agama', 'urutan' => 1],
                ['kode' => 'MTSNU-MRN-UNG', 'nama' => 'Kelas Unggulan', 'deskripsi' => 'Program akselerasi dan pengembangan bakat', 'urutan' => 2],
            ],

            // ─── SMP (Public Junior High) ───
            'SMP-KRK' => [
                ['kode' => 'SMP-KRK-REG', 'nama' => 'Kelas Reguler', 'deskripsi' => 'Program pembelajaran kurikulum nasional', 'urutan' => 1],
                ['kode' => 'SMP-KRK-OLY', 'nama' => 'Kelas Olimpiade', 'deskripsi' => 'Program persiapan olimpiade sains dan matematika', 'urutan' => 2],
            ],

            // ─── MA (Islamic Senior High) — jurusan formal ───
            'MANU-MRN' => [
                ['kode' => 'MANU-IPA', 'nama' => 'IPA (Ilmu Pengetahuan Alam)', 'deskripsi' => 'Program MIPA — Matematika, Fisika, Kimia, Biologi', 'urutan' => 1],
                ['kode' => 'MANU-IPS', 'nama' => 'IPS (Ilmu Pengetahuan Sosial)', 'deskripsi' => 'Program Sosial — Ekonomi, Geografi, Sejarah, Sosiologi', 'urutan' => 2],
                ['kode' => 'MANU-BHS', 'nama' => 'Bahasa Arab', 'deskripsi' => 'Program bahasa Arab intensif dan sastra Arab', 'urutan' => 3],
                ['kode' => 'MANU-AG',  'nama' => 'Keagamaan', 'deskripsi' => 'Program tahfidz, fiqih, ushul fiqih, dan ilmu hadits', 'urutan' => 4],
            ],

            // ─── SMK (Vocational Senior High) — kompetensi keahlian ───
            'SMK-GDG' => [
                ['kode' => 'TKJ', 'nama' => 'Teknik Komputer & Jaringan',  'deskripsi' => 'Instalasi dan konfigurasi jaringan komputer', 'urutan' => 1],
                ['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak',    'deskripsi' => 'Pengembangan aplikasi web, mobile, dan desktop', 'urutan' => 2],
                ['kode' => 'AKT', 'nama' => 'Akuntansi',                   'deskripsi' => 'Pembukuan, perpajakan, dan keuangan bisnis', 'urutan' => 3],
                ['kode' => 'APK', 'nama' => 'Administrasi Perkantoran',    'deskripsi' => 'Tata kelola administrasi dan sekretaris profesional', 'urutan' => 4],
                ['kode' => 'TKR', 'nama' => 'Teknik Kendaraan Ringan',     'deskripsi' => 'Perawatan dan perbaikan kendaraan ringan', 'urutan' => 5],
            ],
        ];

        foreach ($jurusanPerLembaga as $lembagaKode => $jurusanList) {
            $lembagaId = DB::table('lembaga')->where('kode', $lembagaKode)->value('id');

            if (! $lembagaId) {
                $this->command->warn("Lembaga [{$lembagaKode}] tidak ditemukan, lewati.");
                continue;
            }

            foreach ($jurusanList as $data) {
                DB::table('jurusan')->updateOrInsert(
                    ['kode' => $data['kode']],
                    array_merge($data, [
                        'lembaga_id' => $lembagaId,
                        'status'     => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }

            $count = count($jurusanList);
            $this->command->info("  ✓ {$lembagaKode}: {$count} jurusan/program");
        }
    }
}
