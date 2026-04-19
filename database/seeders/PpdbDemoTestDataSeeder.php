<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;

class PpdbDemoTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // --- 1. Pastikan Tahun Pelajaran & Semester Ada ---
        $tahunId = DB::table('tahun_pelajaran')->where('kode_tahun', '2026/2027')->value('id');
        if (!$tahunId) {
            $tahunId = DB::table('tahun_pelajaran')->insertGetId([
                'kode_tahun' => '2026/2027',
                'nama' => 'Tahun Pelajaran 2026/2027',
                'mulai' => '2026-07-01',
                'selesai' => '2027-06-30',
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Tambah Semester
            DB::table('semester')->insert([
                [
                    'tahun_pelajaran_id' => $tahunId,
                    'nama' => 'Semester Ganjil',
                    'semester_ke' => '1',
                    'mulai' => '2026-07-01',
                    'selesai' => '2026-12-31',
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tahun_pelajaran_id' => $tahunId,
                    'nama' => 'Semester Genap',
                    'semester_ke' => '2',
                    'mulai' => '2027-01-01',
                    'selesai' => '2027-06-30',
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        // --- 2. Pastikan Pembukaan PPDB Ada ---
        $pembukaanId = DB::table('pembukaan_ppdb')->where('tahun_pelajaran_id', $tahunId)->value('id');
        if (!$pembukaanId) {
            $pembukaanId = DB::table('pembukaan_ppdb')->insertGetId([
                'tahun_pelajaran_id' => $tahunId,
                'nama' => 'Gelombang Utama 2026',
                'mulai' => now()->startOfMonth()->toDateString(),
                'selesai' => now()->addMonths(3)->toDateString(),
                'status' => 'buka',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // --- 3. Pastikan Jalur Pendaftaran Ada ---
        $jalurZonasiId = DB::table('jalur_pendaftaran')->where('kode_jalur', 'ZONASI')->value('id');
        if (!$jalurZonasiId) {
            $jalurZonasiId = DB::table('jalur_pendaftaran')->insertGetId([
                'pembukaan_ppdb_id' => $pembukaanId,
                'kode_jalur' => 'ZONASI',
                'nama' => 'Zonasi Jarak',
                'kuota' => 30,
                'status' => 'aktif',
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $jalurPrestasiId = DB::table('jalur_pendaftaran')->where('kode_jalur', 'PRESTASI')->value('id');
        if (!$jalurPrestasiId) {
            $jalurPrestasiId = DB::table('jalur_pendaftaran')->insertGetId([
                'pembukaan_ppdb_id' => $pembukaanId,
                'kode_jalur' => 'PRESTASI',
                'nama' => 'Prestasi Akademik/Non-Akademik',
                'kuota' => 60,
                'status' => 'aktif',
                'urutan' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $jalurAfirmasiId = DB::table('jalur_pendaftaran')->where('kode_jalur', 'AFIRMASI')->value('id');
        if (!$jalurAfirmasiId) {
            $jalurAfirmasiId = DB::table('jalur_pendaftaran')->insertGetId([
                'pembukaan_ppdb_id' => $pembukaanId,
                'kode_jalur' => 'AFIRMASI',
                'nama' => 'Afirmasi / Kurang Mampu',
                'kuota' => 10,
                'status' => 'aktif',
                'urutan' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // --- 4. Jadwal Pendaftaran (Aktif Sekarang) ---
        $jalurs = [$jalurZonasiId, $jalurPrestasiId, $jalurAfirmasiId];
        foreach ($jalurs as $jId) {
            DB::table('jadwal_pendaftaran')->updateOrInsert(
                ['jalur_pendaftaran_id' => $jId, 'tipe' => 'pendaftaran'],
                [
                    'nama' => 'Waktu Pendaftaran Online',
                    'mulai' => now()->subDay()->toDateTimeString(),
                    'selesai' => now()->addMonth()->toDateTimeString(),
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            DB::table('jadwal_pendaftaran')->updateOrInsert(
                ['jalur_pendaftaran_id' => $jId, 'tipe' => 'seleksi'], // Ganti 'verifikasi' ke 'seleksi' sesuai ENUM
                [
                    'nama' => 'Masa Verifikasi Data',
                    'mulai' => now()->subDay()->toDateTimeString(),
                    'selesai' => now()->addMonth()->toDateTimeString(),
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            DB::table('jadwal_pendaftaran')->updateOrInsert(
                ['jalur_pendaftaran_id' => $jId, 'tipe' => 'pengumuman'], // Ganti 'verifikasi' ke 'seleksi' sesuai ENUM
                [
                    'nama' => 'Masa Pengumuman Hasil Seleksi',
                    'mulai' => now()->subDay()->toDateTimeString(),
                    'selesai' => now()->addMonth()->toDateTimeString(),
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            DB::table('jadwal_pendaftaran')->updateOrInsert(
                ['jalur_pendaftaran_id' => $jId, 'tipe' => 'daftar_ulang'],
                [
                    'nama' => 'Masa Daftar Ulang',
                    'mulai' => now()->subDay()->toDateTimeString(),
                    'selesai' => now()->addMonth()->toDateTimeString(),
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // --- 5. Syarat Pendaftaran (Dokumen Wajib) ---
        $syarats = [
            ['nama' => 'Kartu Keluarga', 'tipe' => 'dokumen', 'wajib' => true, 'urutan' => 1],
            ['nama' => 'Akta Kelahiran', 'tipe' => 'dokumen', 'wajib' => true, 'urutan' => 2],
            ['nama' => 'Ijazah / SKL', 'tipe' => 'dokumen', 'wajib' => true, 'urutan' => 3],
        ];

        $syaratIds = [];
        foreach ($jalurs as $jId) {
            foreach ($syarats as $s) {
                // Gunakan updateOrInsert atau hapus yg lama untk data syarats agar tidak duplicate jika rerun
                $sId = DB::table('syarat_pendaftaran')->insertGetId(array_merge($s, [
                    'jalur_pendaftaran_id' => $jId,
                    'tahun_pelajaran_id' => $tahunId,
                    'keterangan' => 'Scan dokumen asli berwarna',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $syaratIds[$jId][] = $sId;
            }
        }

        // --- 6. Formulir Pendaftaran & Fields ---
        foreach ($jalurs as $jId) {
            $formId = DB::table('formulir_pendaftaran')->insertGetId([
                'jalur_pendaftaran_id' => $jId,
                'tahun_pelajaran_id' => $tahunId,
                'nama' => 'Formulir Data Tambahan',
                'is_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $fields = [
                [
                    'kode_field' => 'asal_sekolah',
                    'label' => 'Asal Sekolah',
                    'tipe_field' => 'text',
                    'is_required' => true,
                    'urutan' => 1
                ],
                [
                    'kode_field' => 'peminatan',
                    'label' => 'Peminatan / Hobi',
                    'tipe_field' => 'textarea',
                    'is_required' => false,
                    'urutan' => 2
                ],
                [
                    'kode_field' => 'prestasi_tertinggi',
                    'label' => 'Prestasi Tertinggi',
                    'tipe_field' => 'select',
                    'is_required' => false,
                    'urutan' => 3,
                    'opsi' => json_encode([
                        ['value' => 'internasional', 'label' => 'Internasional'],
                        ['value' => 'nasional', 'label' => 'Nasional'],
                        ['value' => 'provinsi', 'label' => 'Provinsi'],
                        ['value' => 'kabupaten', 'label' => 'Kabupaten/Kota']
                    ])
                ]
            ];

            foreach ($fields as $f) {
                DB::table('formulir_field')->insert(array_merge($f, [
                    'formulir_pendaftaran_id' => $formId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $this->command->info('✅ PPDB Demo Test Data Seeded Successfully with fixed columns!');
    }
}
