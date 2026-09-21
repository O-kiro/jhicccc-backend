<?php

namespace Database\Factories;

use App\Models\NewsPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsPost>
 */
class NewsPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(3),
            'title' => fake()->sentence(6),
            'category' => 'Berita',
            'published_on' => now()->subDays(fake()->numberBetween(1, 30)),
            'author' => fake()->name(),
            'excerpt' => fake()->sentence(12),
            'tone' => 'teal',
            'content' => [fake()->paragraph(), fake()->paragraph()],
            'image' => null,
            'is_published' => true,
        ];
    }
}
