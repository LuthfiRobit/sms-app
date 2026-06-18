<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LembagaSeeder extends Seeder
{
    public function run(): void
    {
        $lembaga = [
            ['kode' => 'MINU-KRK',  'nama' => 'MINU Kraksaan',              'jenis' => 'MI',  'urutan' => 1],
            ['kode' => 'MINU-SAM',  'nama' => 'MINU Sunan Ampel Maron',     'jenis' => 'MI',  'urutan' => 2],
            ['kode' => 'MTSNU-SAM', 'nama' => 'MTs NU Sunan Ampel Maron',   'jenis' => 'MTs', 'urutan' => 3],
            ['kode' => 'MTSNU-MRN', 'nama' => 'MTs NU Maron',               'jenis' => 'MTs', 'urutan' => 4],
            ['kode' => 'SMP-KRK',   'nama' => "SMP Ma'arif Kraksaan",        'jenis' => 'SMP', 'urutan' => 5],
            ['kode' => 'MANU-MRN',  'nama' => 'MANU Maron',                 'jenis' => 'MA',  'urutan' => 6],
            ['kode' => 'SMK-GDG',   'nama' => "SMK Ma'arif Gending",         'jenis' => 'SMK', 'urutan' => 7],
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
