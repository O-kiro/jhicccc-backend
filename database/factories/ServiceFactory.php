<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('LYN-###')),
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(10),
            'target' => fake()->randomElement(['Umum', 'Wali Murid', 'Siswa', 'Instansi']),
            'requirements' => fake()->sentence(8),
            'kind' => 'layanan',
            'is_active' => true,
        ];
    }

    public function kunjungan(): static
    {
        return $this->state(fn (): array => ['kind' => 'kunjungan']);
    }
}
