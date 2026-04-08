<?php

namespace Database\Factories;

use App\Models\Transaksi\Pendaftaran;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PendaftaranFactory extends Factory
{
    protected $model = Pendaftaran::class;

    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'peserta_id' => Str::uuid(),
            'jalur_id' => Str::uuid(),
            'no_pendaftaran' => 'REG-' . strtoupper(Str::random(6)),
            'status' => 'Draft',
            'tanggal_daftar' => now(),
        ];
    }
}
