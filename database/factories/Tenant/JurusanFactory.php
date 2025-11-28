<?php

namespace Database\Factories\Tenant;

use App\Models\Tenant\Jurusan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Jurusan>
 */
class JurusanFactory extends Factory
{
    protected $model = Jurusan::class;

    public function definition(): array
    {
        return [
            'kode' => strtoupper(Str::random(2)).$this->faker->unique()->numerify('##'),
            'nama_jurusan' => $this->faker->unique()->words(2, true),
            'deskripsi' => $this->faker->sentence(),
        ];
    }
}
