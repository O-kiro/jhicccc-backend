<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentPermit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentPermit>
 */
class StudentPermitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'teacher_id' => null,
            'kind' => 'keluar_kelas',
            'left_at' => now()->subMinutes(20),
            'returned_at' => null,
            'reason' => fake()->sentence(3),
        ];
    }

    public function returned(): static
    {
        return $this->state(fn (): array => ['returned_at' => now()]);
    }
}
