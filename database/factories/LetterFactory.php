<?php

namespace Database\Factories;

use App\Models\Letter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Letter>
 */
class LetterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'direction' => fake()->randomElement(['masuk', 'keluar']),
            'number' => fake()->unique()->numerify('###/MAN/####'),
            'subject' => fake()->sentence(6),
            'correspondent' => fake()->company(),
            'dated_on' => now()->subDays(fake()->numberBetween(0, 90)),
            'recorded_on' => now(),
            'file_url' => null,
            'note' => null,
        ];
    }
}
