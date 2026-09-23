<?php

namespace Database\Factories;

use App\Models\AlumniForumCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumniForumCategory>
 */
class AlumniForumCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'icon' => 'chat',
            'tone' => 'teal',
            'sort' => 0,
        ];
    }
}
