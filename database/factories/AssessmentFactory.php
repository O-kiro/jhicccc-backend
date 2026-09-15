<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assessment>
 */
class AssessmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'subject_id' => Subject::factory(),
            'title' => fake()->sentence(3),
            'score' => fake()->numberBetween(70, 100),
            'assessed_on' => fake()->dateTimeBetween('-6 months'),
        ];
    }
}
