<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('RG-###')),
            'name' => 'Ruang '.fake()->word(),
            'type' => fake()->randomElement(['Kelas', 'Laboratorium', 'Kantor', 'Aula']),
            'capacity' => fake()->numberBetween(20, 60),
            'condition' => 'baik',
            'note' => null,
        ];
    }
}
