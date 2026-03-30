<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilSekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('profil_sekolah')->insert([
            'npsn' => '20123456',
            'nss' => '101234567890',
            'nama_sekolah' => 'SMA Terpadu Harapan',
            'status_sekolah' => 'Swasta',
            'bentuk_pendidikan' => 'SMA',
            'alamat' => 'Jl. Merdeka No. 10',
            'desa_id' => null,
            'kode_pos' => '12345',
            'telepon' => '021-123456',
            'email' => 'info@smaterpadu.sch.id',
            'website' => 'www.smaterpadu.sch.id',
            'kepala_sekolah' => 'Dr. Budi Santoso',
            'nip_kepsek' => '198001012005011001',
            'logo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
