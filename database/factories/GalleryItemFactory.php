<?php

namespace Database\Factories;

use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryItem>
 */
class GalleryItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'date' => now()->subDays(fake()->numberBetween(1, 90)),
            'category' => 'Kegiatan',
            'tone' => 'teal',
            'image' => null,
            'sort' => 0,
        ];
    }
}
