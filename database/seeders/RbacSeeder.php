<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Sinkronisasi rute menjadi permission
        $this->command->info('Menjalankan proses sinkronisasi rute...');
        Artisan::call('permission:sync');
        $this->command->info(Artisan::output());

        // 2. Buat roles dasar (Super Admin, Kepala Sekolah, Wakasek, Guru, Siswa, dll) sesuai scope
        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Admin', 'scope' => 'global', 'description' => 'Akses Penuh Sistem'],
            ['name' => 'kepala_sekolah', 'display_name' => 'Kepala Sekolah', 'scope' => 'global', 'description' => 'Pemantauan Seluruh Sistem'],
            ['name' => 'wakasek_kurikulum', 'display_name' => 'Wakasek Kurikulum', 'scope' => 'global', 'description' => 'Manajemen Kurikulum'],
            ['name' => 'guru', 'display_name' => 'Guru', 'scope' => 'global', 'description' => 'Guru Pengajar'],
            ['name' => 'wali_kelas', 'display_name' => 'Wali Kelas', 'scope' => 'class', 'description' => 'Wali Kelas Tambahan'],
            ['name' => 'siswa', 'display_name' => 'Siswa', 'scope' => 'global', 'description' => 'Akun Siswa'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(
                ['name' => $r['name']],
                [
                    'display_name' => $r['display_name'],
                    'scope' => $r['scope'],
                    'description' => $r['description'],
                ]
            );
        }

        $superAdminRole = Role::where('name', 'super_admin')->first();

        // 3. Ambil semua permission aktif
        $activePermissions = Permission::where('is_active', true)->pluck('id');

        // 4. Attach semua permission aktif ke role super_admin
        $superAdminRole->permissions()->syncWithoutDetaching($activePermissions);

        // 5. Buat user superadmin jika belum ada
        $developerUser = User::firstOrCreate(
            ['email' => 'superadmin@sms.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'status' => 'active'
            ]
        );

        // 6. Assign role super_admin ke user
        // (Diasumsikan tabel users menggunakan id_user sebagai primary_key tetapi HasRolesAndPermissions Trait dan Model relasinya harus sinkron)
        $developerUser->roles()->syncWithoutDetaching([$superAdminRole->id]);

        $this->command->info('✅ Super Admin role, default roles, and developer user seeded with full permission access.');
    }
}
