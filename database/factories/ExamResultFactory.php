<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamResult>
 */
class ExamResultFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory()->finished(),
            'student_id' => Student::factory(),
            'score' => fake()->numberBetween(60, 100),
            'finished_at' => now()->subDays(fake()->numberBetween(1, 20)),
        ];
    }
}
