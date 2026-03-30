<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembukaanPpdbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pembukaanId = DB::table('pembukaan_ppdb')->insertGetId([
            'tahun_pelajaran_id' => 1,
            'nama' => 'Gelombang 1',
            'mulai' => '2026-04-01',
            'selesai' => '2026-05-31',
            'status' => 'buka',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $jalur = [
            ['kode_jalur' => 'PRESTASI', 'nama' => 'Prestasi', 'kuota' => 30, 'urutan' => 1],
            ['kode_jalur' => 'ZONASI', 'nama' => 'Zonasi', 'kuota' => 50, 'urutan' => 2],
            ['kode_jalur' => 'AFIRMASI', 'nama' => 'Afirmasi', 'kuota' => 20, 'urutan' => 3],
        ];

        foreach ($jalur as $data) {
            DB::table('jalur_pendaftaran')->insert(array_merge($data, [
                'pembukaan_ppdb_id' => $pembukaanId,
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
