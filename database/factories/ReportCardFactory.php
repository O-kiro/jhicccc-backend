<?php

namespace Database\Factories;

use App\Models\ReportCard;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportCard>
 */
class ReportCardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $classSize = fake()->numberBetween(28, 36);

        return [
            'student_id' => Student::factory(),
            'academic_year' => '2025/2026',
            'semester' => 'Ganjil',
            'average_score' => fake()->randomFloat(2, 75, 95),
            'class_rank' => fake()->numberBetween(1, $classSize),
            'class_size' => $classSize,
            'attendance_percentage' => fake()->randomFloat(2, 85, 100),
        ];
    }
}
