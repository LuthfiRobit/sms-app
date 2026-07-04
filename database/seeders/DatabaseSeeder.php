<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            LembagaSeeder::class,
            LembagaGeofenceSeeder::class,    // titik geofence absensi guru (Fase 1 mobile)
            RbacSeeder::class,
            TahunPelajaranSeeder::class,
            KurikulumSeeder::class,
            ProfilSekolahSeeder::class,
            JurusanSeeder::class,
            GuruSeeder::class,
            GuruUserSeeder::class,           // akun login per guru (Fase 1 mobile)
            PpdbDemoTestDataSeeder::class,   // SMK Ma'arif Gending
            PpdbAllLembagaSeeder::class,     // 6 lembaga lainnya
            KelasMobileDemoSeeder::class,    // rombel + siswa + jadwal_kbm (Fase 2/3 mobile)
        ]);
    }
}
