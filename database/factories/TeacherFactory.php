<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name().', S.Pd',
            'nip' => fake()->unique()->numerify('##################'),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
