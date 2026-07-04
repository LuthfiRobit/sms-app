<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Titik geofence absensi guru (GPS + selfie) per lembaga.
 *
 * Koordinat di bawah adalah titik pusat kecamatan tempat lembaga berada
 * (Kraksaan / Maron / Gending, Kabupaten Probolinggo) — perkiraan untuk
 * kebutuhan seed/dev. Admin lembaga wajib menimpanya dengan titik pin
 * GPS akurat lokasi sekolah lewat form Master → Lembaga sebelum absensi
 * guru dipakai di produksi.
 */
class LembagaGeofenceSeeder extends Seeder
{
    public function run(): void
    {
        $geofence = [
            'MINU-KRK' => ['lat' => -7.7503, 'lng' => 113.4198], // Kec. Kraksaan
            'MINU-SAM' => ['lat' => -7.8583, 'lng' => 113.3782], // Kec. Maron
            'MTSNU-SAM' => ['lat' => -7.8583, 'lng' => 113.3782], // Kec. Maron
            'MTSNU-MRN' => ['lat' => -7.8583, 'lng' => 113.3782], // Kec. Maron
            'SMP-KRK' => ['lat' => -7.7503, 'lng' => 113.4198], // Kec. Kraksaan
            'MANU-MRN' => ['lat' => -7.8583, 'lng' => 113.3782], // Kec. Maron
            'SMK-GDG' => ['lat' => -7.7284, 'lng' => 113.3405], // Kec. Gending
        ];

        foreach ($geofence as $kode => $titik) {
            DB::table('lembaga')->where('kode', $kode)->update([
                'latitude' => $titik['lat'],
                'longitude' => $titik['lng'],
                'radius_meter' => 150,
                'jam_masuk_batas' => '07:00:00',
                'updated_at' => now(),
            ]);
        }

        $this->command->info('LembagaGeofenceSeeder: titik geofence & jam masuk diset untuk 7 lembaga.');
    }
}
