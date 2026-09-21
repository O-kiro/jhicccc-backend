<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'tag' => 'Unggulan',
            'icon' => 'research',
            'color' => 'teal',
            'description' => fake()->sentence(10),
            'points' => [fake()->sentence(4)],
            'detail' => [fake()->paragraph()],
            'activities' => [fake()->sentence(3)],
            'sort' => 0,
        ];
    }
}
