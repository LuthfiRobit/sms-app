<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePesertaSeeder extends Seeder
{
    public function run(): void
    {
        // Kolom PK tabel roles = id (auto-increment)
        // Kolom name bersifat unique — digunakan sebagai kunci pencarian
        // Idempotent: aman dijalankan berkali-kali
        Role::firstOrCreate(
            ['name' => 'peserta'],
            [
                'name'         => 'peserta',
                'display_name' => 'Peserta PPDB',
                'description'  => 'Role untuk peserta yang mendaftar melalui portal PPDB',
                'scope'        => 'personal', // scope personal sesuai enum di migration
            ]
        );

        $this->command->info('✅ Role peserta berhasil dibuat atau sudah ada.');
    }
}
