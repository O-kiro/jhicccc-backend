<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'date' => now()->toDateString(),
            'code' => 'H',
            'check_in_at' => '06:55:00',
            'source' => 'fingerprint',
            'note' => null,
        ];
    }
}
