<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TahunPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tp = \App\Models\Master\TahunPelajaran::create([
            'kode_tahun' => '2026/2027',
            'nama' => 'Tahun Pelajaran 2026/2027',
            'mulai' => '2026-07-01',
            'selesai' => '2027-06-30',
            'status' => 'aktif',
        ]);

        \App\Models\Master\Semester::create([
            'tahun_pelajaran_id' => $tp->id,
            'nama' => 'Semester Ganjil',
            'semester_ke' => 1,
            'mulai' => '2026-07-01',
            'selesai' => '2026-12-31',
            'status' => 'aktif',
        ]);

        \App\Models\Master\Semester::create([
            'tahun_pelajaran_id' => $tp->id,
            'nama' => 'Semester Genap',
            'semester_ke' => 2,
            'mulai' => '2027-01-01',
            'selesai' => '2027-06-30',
            'status' => 'aktif',
        ]);
    }
}
