<?php

namespace Database\Factories;

use App\Models\CounselingSession;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CounselingSession>
 */
class CounselingSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'teacher_id' => null,
            'held_on' => now()->subDays(fake()->numberBetween(0, 30)),
            'category' => fake()->randomElement(['Akademik', 'Pribadi', 'Karier', 'Sosial']),
            'summary' => fake()->paragraph(),
            'follow_up' => null,
            'is_confidential' => false,
        ];
    }
}
