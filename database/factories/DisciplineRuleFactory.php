<?php

namespace Database\Factories;

use App\Models\DisciplineRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisciplineRule>
 */
class DisciplineRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('TT-###')),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(10),
            'kind' => 'pelanggaran',
            'category' => fake()->randomElement(['Kedisiplinan', 'Kerapian', 'Ibadah']),
            'points' => fake()->numberBetween(5, 50),
            'is_active' => true,
        ];
    }

    public function penghargaan(): static
    {
        return $this->state(fn (): array => ['kind' => 'penghargaan']);
    }
}
