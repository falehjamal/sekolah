<?php

namespace Database\Factories\Tenant;

use App\Models\Tenant\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kelas>
 */
class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    public function definition(): array
    {
        return [
            'nama_kelas' => strtoupper($this->faker->randomLetter()).strtoupper($this->faker->randomLetter()).' '.$this->faker->randomDigitNotNull(),
            'tingkat' => $this->faker->numberBetween(7, 12),
            'jurusan_id' => null,
        ];
    }
}
