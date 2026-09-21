<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseModule>
 */
class CourseModuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'number' => 1,
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(10),
            'url' => null,
        ];
    }

    /** Modul yang materinya sudah ditautkan guru. */
    public function linked(): static
    {
        return $this->state(fn (): array => ['url' => 'https://drive.google.com/file/d/contoh']);
    }
}
