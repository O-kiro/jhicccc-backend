<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->numberBetween(7, 13);

        return [
            'classroom_id' => Classroom::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
            'day_of_week' => fake()->numberBetween(1, 5),
            'starts_at' => sprintf('%02d:00:00', $start),
            'ends_at' => sprintf('%02d:30:00', $start + 1),
            'meeting_url' => null,
        ];
    }

    public function liveNow(): static
    {
        return $this->state(fn (array $attributes): array => [
            'day_of_week' => now()->dayOfWeekIso,
            'starts_at' => now()->subMinutes(10)->format('H:i:s'),
            'ends_at' => now()->addMinutes(50)->format('H:i:s'),
        ]);
    }
}
