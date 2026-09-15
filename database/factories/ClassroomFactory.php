<?php

namespace Database\Factories;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = fake()->randomElement(['X', 'XI', 'XII']);

        return [
            'name' => $level.'-'.fake()->randomLetter(),
            'level' => $level,
            'academic_year' => '2025/2026',
            'homeroom_teacher_id' => null,
        ];
    }
}
