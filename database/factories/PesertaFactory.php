<?php

namespace Database\Factories;

use App\Models\Peserta\Peserta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PesertaFactory extends Factory
{
    protected $model = Peserta::class;

    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'user_id' => Str::uuid(),
            'nama_lengkap' => $this->faker->name,
            'nisn' => $this->faker->unique()->numerify('##########'),
            'nik' => $this->faker->unique()->numerify('################'),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'tempat_lahir' => $this->faker->city,
            'tanggal_lahir' => $this->faker->date(),
        ];
    }
}
