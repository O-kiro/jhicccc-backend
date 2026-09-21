<?php

namespace Database\Factories;

use App\Models\DisciplineRecord;
use App\Models\DisciplineRule;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisciplineRecord>
 */
class DisciplineRecordFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'discipline_rule_id' => DisciplineRule::factory(),
            'teacher_id' => null,
            'occurred_on' => now()->subDays(fake()->numberBetween(0, 60)),
            'points' => fake()->numberBetween(5, 50),
            'note' => null,
        ];
    }
}
