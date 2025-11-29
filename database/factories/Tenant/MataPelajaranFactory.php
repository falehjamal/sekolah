<?php

namespace Database\Factories\Tenant;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant\MataPelajaran>
 */
class MataPelajaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => strtoupper($this->faker->bothify('MP-###')),
            'nama_mapel' => $this->faker->unique()->words(2, true),
            'kurikulum' => $this->faker->randomElement(['Merdeka', '2013', 'Nasional', null]),
            'status' => $this->faker->randomElement(['aktif', 'nonaktif']),
        ];
    }
}
