<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        // Demo jurusan diasosiasikan ke SMK Ma'arif Gending
        $smkId = DB::table('lembaga')->where('kode', 'SMK-GDG')->value('id');

        $jurusan = [
            ['kode' => 'TKJ', 'nama' => 'Teknik Komputer & Jaringan', 'status' => 'aktif', 'urutan' => 1],
            ['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak',   'status' => 'aktif', 'urutan' => 2],
            ['kode' => 'AKT', 'nama' => 'Akuntansi',                  'status' => 'aktif', 'urutan' => 3],
            ['kode' => 'APK', 'nama' => 'Administrasi Perkantoran',   'status' => 'aktif', 'urutan' => 4],
            ['kode' => 'TKR', 'nama' => 'Teknik Kendaraan Ringan',    'status' => 'aktif', 'urutan' => 5],
        ];

        foreach ($jurusan as $data) {
            DB::table('jurusan')->updateOrInsert(
                ['kode' => $data['kode']],
                array_merge($data, [
                    'lembaga_id' => $smkId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
