<?php

namespace Database\Factories;

use App\Models\DigitalService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalService>
 */
class DigitalServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => null,
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(8),
            'href' => '/layanan',
            'icon' => 'rdm',
            'tone' => 'teal',
            'sort' => 0,
            'is_active' => true,
        ];
    }
}
