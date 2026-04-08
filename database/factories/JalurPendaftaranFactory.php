<?php

namespace Database\Factories;

use App\Models\Ppdb\JalurPendaftaran;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class JalurPendaftaranFactory extends Factory
{
    protected $model = JalurPendaftaran::class;

    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'nama_jalur' => 'Reguler',
            'kuota' => 50,
            'status' => 'Aktif',
            'pembukaan_ppdb_id' => Str::uuid(),
        ];
    }
}
