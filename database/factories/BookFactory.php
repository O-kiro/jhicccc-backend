<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('PUS-####')),
            'title' => Str::title(fake()->unique()->words(3, true)),
            'author' => fake()->name(),
            'description' => fake()->sentence(12),
            'category' => fake()->randomElement(['Studi Islam', 'Sains & Teknologi', 'Humaniora']),
            'total_pages' => fake()->numberBetween(120, 400),
        ];
    }
}
