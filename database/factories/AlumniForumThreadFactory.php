<?php

namespace Database\Factories;

use App\Models\AlumniAccount;
use App\Models\AlumniForumCategory;
use App\Models\AlumniForumThread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumniForumThread>
 */
class AlumniForumThreadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alumni_forum_category_id' => AlumniForumCategory::factory(),
            'alumni_account_id' => AlumniAccount::factory(),
            'title' => fake()->sentence(6),
            'body' => fake()->paragraph(),
            'like_count' => 0,
        ];
    }
}
