<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => 'Latihan '.fake()->words(3, true),
            'description' => fake()->sentence(),
            'url' => null,
            'due_at' => now()->addDays(3)->setTime(23, 59),
        ];
    }
}
