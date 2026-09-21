<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('BRG-#####')),
            'name' => fake()->randomElement(['Proyektor', 'Kursi', 'Meja', 'Komputer', 'AC']),
            'category' => fake()->randomElement(['Elektronik', 'Mebel', 'Alat Peraga']),
            'room_id' => null,
            'quantity' => fake()->numberBetween(1, 40),
            'acquired_on' => now()->subYears(fake()->numberBetween(0, 5)),
            'price' => fake()->numberBetween(200_000, 20_000_000),
            'condition' => 'baik',
            'note' => null,
        ];
    }
}
