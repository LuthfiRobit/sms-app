<?php

namespace Database\Seeders;

use App\Models\Master\Guru;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Membuat satu akun login per guru (role 'guru') dan menautkannya ke
 * record guru lewat guru.user_id — jembatan yang dibutuhkan aplikasi
 * mobile guru untuk resolve "user yang login ini guru yang mana".
 *
 * Kredensial dev/demo (bukan untuk produksi):
 *   username: guru{id}   password: password
 */
class GuruUserSeeder extends Seeder
{
    public function run(): void
    {
        $guruRole = Role::where('name', 'guru')->first();

        if (! $guruRole) {
            $this->command->warn('GuruUserSeeder: role "guru" belum ada, jalankan RbacSeeder dulu.');

            return;
        }

        $dibuat = 0;

        Guru::whereNull('user_id')->each(function (Guru $guru) use ($guruRole, &$dibuat) {
            $username = 'guru'.$guru->id;

            $user = User::firstOrCreate(
                ['username' => $username],
                [
                    'name' => $guru->nama_lengkap,
                    'email' => "{$username}@marifat.sch.id",
                    'no_hp' => $guru->no_hp,
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]
            );

            $user->roles()->syncWithoutDetaching([$guruRole->id]);
            $guru->update(['user_id' => $user->id_user]);

            $dibuat++;
        });

        $this->command->info("GuruUserSeeder: {$dibuat} akun guru dibuat/ditautkan (login: guru{id} / password).");
    }
}
