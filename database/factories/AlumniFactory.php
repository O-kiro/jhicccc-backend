<?php

namespace Database\Factories;

use App\Models\Alumni;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alumni>
 */
class AlumniFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'year' => fake()->numberBetween(2010, 2025),
            'achievement' => fake()->sentence(5),
            'field' => 'Kedokteran',
            'tone' => 'teal',
            'quote' => fake()->sentence(10),
            'sort' => 0,
        ];
    }
}
