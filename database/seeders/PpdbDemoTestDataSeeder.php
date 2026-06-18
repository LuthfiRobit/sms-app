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
        // Demo PPDB diasosiasikan ke SMK Ma'arif Gending
        $smkLembagaId = DB::table('lembaga')->where('kode', 'SMK-GDG')->value('id');
        $pembukaanId = DB::table('pembukaan_ppdb')->where('tahun_pelajaran_id', $tahunId)->value('id');
        if (!$pembukaanId) {
            $pembukaanId = DB::table('pembukaan_ppdb')->insertGetId([
                'lembaga_id'         => $smkLembagaId,
                'tahun_pelajaran_id' => $tahunId,
                'nama'               => 'Gelombang Utama 2026',
                'mulai'              => now()->startOfMonth()->toDateString(),
                'selesai'            => now()->addMonths(3)->toDateString(),
                'status'             => 'buka',
                'created_at'         => now(),
                'updated_at'         => now(),
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
                $existing = DB::table('syarat_pendaftaran')
                    ->where('jalur_pendaftaran_id', $jId)
                    ->where('nama', $s['nama'])
                    ->value('id');

                if ($existing) {
                    $syaratIds[$jId][] = $existing;
                } else {
                    $syaratIds[$jId][] = DB::table('syarat_pendaftaran')->insertGetId(array_merge($s, [
                        'jalur_pendaftaran_id' => $jId,
                        'tahun_pelajaran_id' => $tahunId,
                        'keterangan' => 'Scan dokumen asli berwarna',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }
        }

        // --- 6. Formulir Pendaftaran & Fields ---
        $fields = [
            [
                'kode_field' => 'asal_sekolah',
                'label' => 'Asal Sekolah',
                'tipe_field' => 'text',
                'is_required' => true,
                'urutan' => 1,
                'opsi' => null,
            ],
            [
                'kode_field' => 'peminatan',
                'label' => 'Peminatan / Hobi',
                'tipe_field' => 'textarea',
                'is_required' => false,
                'urutan' => 2,
                'opsi' => null,
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
                    ['value' => 'kabupaten', 'label' => 'Kabupaten/Kota'],
                ]),
            ],
        ];

        foreach ($jalurs as $jId) {
            $formId = DB::table('formulir_pendaftaran')
                ->where('jalur_pendaftaran_id', $jId)
                ->where('tahun_pelajaran_id', $tahunId)
                ->value('id');

            if (!$formId) {
                $formId = DB::table('formulir_pendaftaran')->insertGetId([
                    'jalur_pendaftaran_id' => $jId,
                    'tahun_pelajaran_id' => $tahunId,
                    'nama' => 'Formulir Data Tambahan',
                    'is_aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($fields as $f) {
                    DB::table('formulir_field')->insert(array_merge($f, [
                        'formulir_pendaftaran_id' => $formId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }
        }

        // --- 7. Biaya Registrasi per Jalur ---
        $biayaConfig = [
            $jalurZonasiId   => ['nama' => 'Biaya Pendaftaran Zonasi', 'nominal' => 150000],
            $jalurPrestasiId => ['nama' => 'Biaya Pendaftaran Prestasi', 'nominal' => 150000],
            $jalurAfirmasiId => ['nama' => 'Biaya Pendaftaran Afirmasi', 'nominal' => 0],
        ];

        foreach ($biayaConfig as $jId => $biaya) {
            DB::table('biaya_registrasi')->updateOrInsert(
                ['jalur_pendaftaran_id' => $jId, 'tahun_pelajaran_id' => $tahunId],
                [
                    'nama' => $biaya['nama'],
                    'nominal' => $biaya['nominal'],
                    'deskripsi' => $biaya['nominal'] === 0 ? 'Gratis untuk jalur afirmasi' : 'Biaya administrasi pendaftaran',
                    'is_aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // --- 8. Kuota Jurusan per Jalur ---
        $jurusanIds = DB::table('jurusan')->where('status', 'aktif')->pluck('id');

        $kuotaConfig = [
            $jalurZonasiId   => 6,   // 6 per jurusan × 5 jurusan = 30 total (sesuai kuota jalur)
            $jalurPrestasiId => 12,  // 12 per jurusan × 5 jurusan = 60 total
            $jalurAfirmasiId => 2,   // 2 per jurusan × 5 jurusan = 10 total
        ];

        foreach ($kuotaConfig as $jId => $kuotaPerJurusan) {
            foreach ($jurusanIds as $jurusanId) {
                DB::table('kuota_jurusan')->updateOrInsert(
                    [
                        'tahun_pelajaran_id'   => $tahunId,
                        'jalur_pendaftaran_id' => $jId,
                        'jurusan_id'           => $jurusanId,
                    ],
                    [
                        'kuota'      => $kuotaPerJurusan,
                        'terisi'     => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('✅ PPDB Demo Test Data seeded: jalur, jadwal, syarat, formulir, biaya, kuota jurusan.');
    }
}
