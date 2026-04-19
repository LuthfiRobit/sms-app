<?php

namespace Database\Factories;

use App\Models\Ppdb\PembukaanPpdb;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PembukaanPpdbFactory extends Factory
{
    protected $model = PembukaanPpdb::class;

    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => now()->subDays(1),
            'tanggal_selesai' => now()->addDays(30),
            'status' => 'Aktif',
            'kuota_global' => 100,
        ];
    }
}
