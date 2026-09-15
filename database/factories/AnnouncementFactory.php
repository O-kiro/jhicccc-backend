<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'published_at' => fake()->dateTimeBetween('-2 months'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => ['published_at' => null]);
    }
}
