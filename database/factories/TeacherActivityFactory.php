<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\TeacherActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherActivity>
 */
class TeacherActivityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'classroom_id' => null,
            'date' => now()->toDateString(),
            'activity' => fake()->sentence(6),
            'photo_path' => null,
        ];
    }
}
