<?php

namespace Database\Factories;

use App\Models\Extracurricular;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Extracurricular>
 */
class ExtracurricularFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'category' => 'Olahraga',
            'description' => fake()->sentence(8),
            'icon' => 'ball',
            'sort' => 0,
        ];
    }
}
