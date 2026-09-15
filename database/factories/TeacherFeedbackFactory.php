<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherFeedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherFeedback>
 */
class TeacherFeedbackFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'teacher_id' => Teacher::factory(),
            'role' => fake()->randomElement(['Wali Kelas', 'Guru Fisika', 'Guru Matematika']),
            'body' => fake()->paragraph(),
        ];
    }
}
