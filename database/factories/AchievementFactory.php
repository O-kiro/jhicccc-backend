<?php

namespace Database\Factories;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'student' => fake()->name(),
            'level' => 'Nasional',
            'year' => (int) now()->format('Y'),
            'organizer' => fake()->company(),
            'field' => 'Robotik',
            'sort' => 0,
        ];
    }
}
