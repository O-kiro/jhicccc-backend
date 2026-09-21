<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\TeachingJournal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeachingJournal>
 */
class TeachingJournalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'teacher_id' => null,
            'date' => now()->toDateString(),
            'topic' => fake()->sentence(5),
            'note' => null,
            'present_count' => fake()->numberBetween(25, 32),
        ];
    }
}
