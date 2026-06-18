<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LembagaSeeder extends Seeder
{
    public function run(): void
    {
        $lembaga = [
            [
                'kode'            => 'MINU-KRK',
                'nama'            => 'MINU Kraksaan',
                'npsn'            => '60713842',
                'jenis'           => 'MI',
                'alamat'          => 'Jl. KH. Ahmad Dahlan No. 12, Kraksaan, Probolinggo, Jawa Timur 67282',
                'telepon'         => '0335-841234',
                'email'           => 'minu.kraksaan@gmail.com',
                'kepala_sekolah'  => 'Ahmad Rifa\'i, S.Pd.I',
                'urutan'          => 1,
            ],
            [
                'kode'            => 'MINU-SAM',
                'nama'            => 'MINU Sunan Ampel Maron',
                'npsn'            => '60713901',
                'jenis'           => 'MI',
                'alamat'          => 'Jl. Raya Sunan Ampel No. 45, Maron, Probolinggo, Jawa Timur 67273',
                'telepon'         => '0335-651234',
                'email'           => 'minu.sunanampel.maron@gmail.com',
                'kepala_sekolah'  => 'H. Mustofa, S.Pd.I',
                'urutan'          => 2,
            ],
            [
                'kode'            => 'MTSNU-SAM',
                'nama'            => 'MTs NU Sunan Ampel Maron',
                'npsn'            => '20566781',
                'jenis'           => 'MTs',
                'alamat'          => 'Jl. Pesantren Sunan Ampel No. 7, Maron, Probolinggo, Jawa Timur 67273',
                'telepon'         => '0335-789012',
                'email'           => 'mtsnu.sunanampel@gmail.com',
                'kepala_sekolah'  => 'Drs. H. Masruri, M.Pd.I',
                'urutan'          => 3,
            ],
            [
                'kode'            => 'MTSNU-MRN',
                'nama'            => 'MTs NU Maron',
                'npsn'            => '20566823',
                'jenis'           => 'MTs',
                'alamat'          => 'Jl. Raya Maron-Probolinggo Km. 5, Maron, Probolinggo, Jawa Timur 67273',
                'telepon'         => '0335-456789',
                'email'           => 'mtsnu.maron@gmail.com',
                'kepala_sekolah'  => 'KH. Abdurrahman, S.Pd.I, M.Pd',
                'urutan'          => 4,
            ],
            [
                'kode'            => 'SMP-KRK',
                'nama'            => "SMP Ma'arif Kraksaan",
                'npsn'            => '20567124',
                'jenis'           => 'SMP',
                'alamat'          => 'Jl. KH. Hasyim Asy\'ari No. 20, Kraksaan, Probolinggo, Jawa Timur 67282',
                'telepon'         => '0335-345678',
                'email'           => 'smp.maarif.kraksaan@gmail.com',
                'kepala_sekolah'  => 'Sugiyono, M.Pd',
                'urutan'          => 5,
            ],
            [
                'kode'            => 'MANU-MRN',
                'nama'            => 'MANU Maron',
                'npsn'            => '20566956',
                'jenis'           => 'MA',
                'alamat'          => 'Jl. KH. Wahid Hasyim No. 15, Maron, Probolinggo, Jawa Timur 67273',
                'telepon'         => '0335-567890',
                'email'           => 'manu.maron@gmail.com',
                'kepala_sekolah'  => 'Drs. H. Syafi\'i Noer, M.Ag',
                'urutan'          => 6,
            ],
            [
                'kode'            => 'SMK-GDG',
                'nama'            => "SMK Ma'arif Gending",
                'npsn'            => '20567385',
                'jenis'           => 'SMK',
                'alamat'          => 'Jl. Raya Gending No. 88, Gending, Probolinggo, Jawa Timur 67272',
                'telepon'         => '0335-678901',
                'email'           => 'smk.maarif.gending@gmail.com',
                'kepala_sekolah'  => 'Ir. H. Moh. Hasan, M.T',
                'urutan'          => 7,
            ],
        ];

        foreach ($lembaga as $data) {
            DB::table('lembaga')->updateOrInsert(
                ['kode' => $data['kode']],
                array_merge($data, [
                    'status'     => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
