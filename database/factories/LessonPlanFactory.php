<?php

namespace Database\Factories;

use App\Models\LessonPlan;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonPlan>
 */
class LessonPlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'subject_id' => null,
            'classroom_id' => null,
            'title' => fake()->sentence(3),
            'time_range' => '07.45–09.15',
            'url' => null,
            'note' => null,
            'status' => 'aktif',
        ];
    }

    public function arsip(): static
    {
        return $this->state(fn (): array => ['status' => 'arsip']);
    }
}
