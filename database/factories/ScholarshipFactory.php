<?php

namespace Database\Factories;

use App\Models\Scholarship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Scholarship>
 */
class ScholarshipFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => fake()->randomElement(['Sains & Teknologi', 'Olimpiade Sains', 'Keagamaan & Tahfidz']),
            'name' => 'Beasiswa '.fake()->words(2, true),
            'quota' => fake()->numberBetween(10, 50),
            'benefits' => fake()->sentence(),
            'target' => fake()->sentence(4),
            'deadline' => now()->addMonths(2),
            'status' => 'dibuka',
            'applicants' => fake()->numberBetween(0, 100),
            'verified' => 0,
            'url' => null,
            'sort' => 0,
        ];
    }

    public function ditutup(): static
    {
        return $this->state(fn (): array => ['status' => 'ditutup', 'quota' => 0]);
    }
}
