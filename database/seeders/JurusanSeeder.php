<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusan = [
            ['kode' => 'TKJ', 'nama' => 'Teknik Komputer & Jaringan', 'status' => 'aktif', 'urutan' => 1],
            ['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak', 'status' => 'aktif', 'urutan' => 2],
            ['kode' => 'AKT', 'nama' => 'Akuntansi', 'status' => 'aktif', 'urutan' => 3],
            ['kode' => 'APK', 'nama' => 'Administrasi Perkantoran', 'status' => 'aktif', 'urutan' => 4],
            ['kode' => 'TKR', 'nama' => 'Teknik Kendaraan Ringan', 'status' => 'aktif', 'urutan' => 5],
        ];

        foreach ($jurusan as $data) {
            DB::table('jurusan')->updateOrInsert(
                ['kode' => $data['kode']],
                array_merge($data, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
