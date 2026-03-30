<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Master\Kurikulum;

class KurikulumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kurikulum::create([
            'nama_kurikulum' => 'Kurikulum Merdeka',
            'versi' => 'Rev. 2024',
            'status' => 'aktif',
            'keterangan' => 'Kurikulum nasional yang fokus pada keleluasaan pendidik dan pembelajaran berbasis proyek (P5).'
        ]);

        Kurikulum::create([
            'nama_kurikulum' => 'Kurikulum 2013',
            'versi' => 'Edisi Revisi',
            'status' => 'nonaktif',
            'keterangan' => 'Kurikulum nasional sebelumnya dengan pendekatan saintifik.'
        ]);
    }
}
