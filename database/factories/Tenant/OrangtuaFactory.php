<?php

namespace Database\Factories\Tenant;

use App\Models\Tenant\Orangtua;
use App\Models\Tenant\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Orangtua>
 */
class OrangtuaFactory extends Factory
{
    protected $model = Orangtua::class;

    public function definition(): array
    {
        return [
            'siswa_id' => Siswa::factory(),
            'nama' => $this->faker->name(),
            'hubungan' => $this->faker->randomElement(['ayah', 'ibu', 'wali']),
            'no_hp' => $this->faker->phoneNumber(),
            'pekerjaan' => $this->faker->jobTitle(),
            'alamat' => $this->faker->address(),
        ];
    }
}
