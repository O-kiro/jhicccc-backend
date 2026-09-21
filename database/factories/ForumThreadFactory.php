<?php

namespace Database\Factories;

use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ForumThread>
 */
class ForumThreadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'forum_category_id' => ForumCategory::factory(),
            'student_id' => Student::factory(),
            'title' => Str::title(fake()->words(5, true)),
            'body' => fake()->paragraph(),
            'like_count' => fake()->numberBetween(0, 120),
        ];
    }
}
