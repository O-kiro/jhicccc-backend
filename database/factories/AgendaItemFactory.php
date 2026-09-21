<?php

namespace Database\Factories;

use App\Models\AgendaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgendaItem>
 */
class AgendaItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => now()->addDays(fake()->numberBetween(1, 60)),
            'title' => fake()->sentence(4),
            'category' => 'Umum',
        ];
    }
}
