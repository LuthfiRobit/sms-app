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
                'kuota' => 100,
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
                'kuota' => 50,
                'status' => 'aktif',
                'urutan' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // --- 4. Jadwal Pendaftaran (Aktif Sekarang) ---
        $jalurs = [$jalurZonasiId, $jalurPrestasiId];
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
        }

        // --- 5. Syarat Pendaftaran (Dokumen Wajib) ---
        $syarats = [
            ['nama' => 'Kartu Keluarga', 'tipe' => 'dokumen', 'wajib' => true],
            ['nama' => 'Akta Kelahiran', 'tipe' => 'dokumen', 'wajib' => true],
            ['nama' => 'Ijazah / SKL', 'tipe' => 'dokumen', 'wajib' => true],
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

        // --- 7. Peserta & Pendaftaran (Dummy 10+) ---
        $adminUserId = DB::table('users')->where('email', 'superadmin@sms.com')->value('id_user');

        $statuses = ['draft', 'submit', 'verifikasi', 'lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_tetap', 'submit', 'verifikasi', 'submit'];

        foreach ($statuses as $index => $status) {
            $name = $faker->name;
            $email = strtolower(Str::slug($name)) . ($index + 1) . '@example.com';

            // Create user account
            $userId = DB::table('users')->insertGetId([
                'name' => $name,
                'username' => 'peserta' . ($index + 1),
                'email' => $email,
                'password' => Hash::make('password'),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_user');

            // Create Peserta
            $pesertaId = DB::table('peserta')->insertGetId([
                'user_id' => $userId,
                'nisn' => rand(1000000000, 9999999999),
                'nik' => rand(1000000000000000, 9999999999999999),
                'nama_lengkap' => $name,
                'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->dateTimeBetween('-16 years', '-14 years')->format('Y-m-d'),
                'agama' => $faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
                'kebutuhan_khusus' => 'Tidak Ada',
                'no_kk' => rand(1000000000000000, 9999999999999999),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Alamat
            DB::table('peserta_alamat')->insert([
                'peserta_id' => $pesertaId,
                'alamat' => $faker->address,
                'desa_kelurahan' => $faker->streetName, // streetName sebagai desa
                'kecamatan' => $faker->city, // city sebagai kecamatan
                'kabupaten_kota' => $faker->city,
                'provinsi' => $faker->state,
                'rt' => rand(1, 20),
                'rw' => rand(1, 20),
                'kode_pos' => $faker->postcode,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Kontak
            DB::table('peserta_kontak')->insert([
                'peserta_id' => $pesertaId,
                'no_hp' => substr($faker->phoneNumber, 0, 15),
                'email' => $email,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Orang Tua (Ayah & Ibu)
            $ortu = [
                ['tipe' => 'ayah', 'pria' => true, 'nik' => rand(1000000000000000, 9999999999999999)],
                ['tipe' => 'ibu', 'pria' => false, 'nik' => rand(1000000000000000, 9999999999999999)]
            ];

            foreach ($ortu as $o) {
                DB::table('peserta_orang_tua')->insert([
                    'peserta_id' => $pesertaId,
                    'tipe' => $o['tipe'],
                    'nama' => $faker->name($o['pria'] ? 'male' : 'female'),
                    'nik' => $o['nik'],
                    'pekerjaan' => substr($faker->jobTitle, 0, 50),
                    'penghasilan' => $faker->randomElement(['1-2 jt', '2-5 jt', '> 5 jt']), // Shortened for string(20)
                    'pendidikan' => $faker->randomElement(['SMA', 'D3', 'S1', 'S2']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Periodik
            DB::table('peserta_periodik')->insert([
                'peserta_id' => $pesertaId,
                'tahun_pelajaran_id' => $tahunId,
                'tinggi_badan' => rand(140, 180),
                'berat_badan' => rand(35, 75),
                'jarak_rumah' => rand(1, 20),
                'waktu_tempuh' => rand(5, 60),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // --- SEED PENDAFTARAN ---
            $pathId = ($index % 2 == 0) ? $jalurZonasiId : $jalurPrestasiId;
            // No pendaftaran: PPDB + Year + Rand to reach uniqueness
            $noPendaftaran = 'PPDB' . Carbon::now()->year . str_pad($index . rand(1000, 9999), 5, '0', STR_PAD_LEFT);
            // Limit length to 20
            $noPendaftaran = substr($noPendaftaran, 0, 20);

            $pendaftaranId = DB::table('pendaftaran')->insertGetId([
                'no_pendaftaran' => $noPendaftaran,
                'peserta_id' => $pesertaId,
                'jalur_pendaftaran_id' => $pathId,
                'tahun_pelajaran_id' => $tahunId,
                'status' => $status,
                'tanggal_daftar' => ($status != 'draft') ? Carbon::now()->subDays(rand(1, 10)) : null,
                'verified_by' => in_array($status, ['verifikasi', 'lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_tetap']) ? $adminUserId : null,
                'verified_at' => in_array($status, ['verifikasi', 'lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_tetap']) ? Carbon::now() : null,
                'catatan_verifikasi' => ($status == 'verifikasi') ? 'Data sudah dicek oleh petugas.' : (($status == 'tidak_lulus') ? 'Nilai tidak mencukupi standar kelulusan.' : null),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Field Values (jika submitted atau lebih)
            if ($status != 'draft') {
                $formFields = DB::table('formulir_field')
                    ->join('formulir_pendaftaran', 'formulir_field.formulir_pendaftaran_id', '=', 'formulir_pendaftaran.id')
                    ->where('formulir_pendaftaran.jalur_pendaftaran_id', $pathId)
                    ->select('formulir_field.id', 'formulir_field.kode_field')
                    ->get();

                foreach ($formFields as $field) {
                    $val = match ($field->kode_field) {
                        'asal_sekolah' => 'SMP Negeri ' . rand(1, 10) . ' Kab. Dummy',
                        'peminatan' => 'Tertarik pada pengembangan web dan robotika.',
                        'prestasi_tertinggi' => $faker->randomElement(['nasional', 'provinsi']),
                        default => $faker->word
                    };

                    DB::table('pendaftaran_field_value')->insert([
                        'pendaftaran_id' => $pendaftaranId,
                        'formulir_field_id' => $field->id,
                        'value' => $val,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Dokumen dummy
                $requiredSyarat = $syaratIds[$pathId] ?? [];
                foreach ($requiredSyarat as $sId) {
                    DB::table('dokumen_peserta')->insert([
                        'pendaftaran_id' => $pendaftaranId,
                        'syarat_pendaftaran_id' => $sId,
                        'nama_file' => 'berkas_test_' . rand(100, 999) . '.pdf',
                        'path_file' => 'pendaftaran/' . $noPendaftaran . '/doc_' . $sId . '.pdf',
                        'mime_type' => 'application/pdf',
                        'ukuran_file' => rand(100000, 2000000),
                        'status_verifikasi' => ($status != 'submit') ? 'valid' : 'pending',
                        'verified_by' => in_array($status, ['verifikasi', 'lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_tetap']) ? $adminUserId : null,
                        'verified_at' => in_array($status, ['verifikasi', 'lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_tetap']) ? Carbon::now() : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('✅ PPDB Demo Test Data Seeded Successfully with fixed columns!');
    }
}
