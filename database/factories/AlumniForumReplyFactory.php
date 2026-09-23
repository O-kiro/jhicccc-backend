<?php

namespace Database\Factories;

use App\Models\AlumniAccount;
use App\Models\AlumniForumReply;
use App\Models\AlumniForumThread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumniForumReply>
 */
class AlumniForumReplyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alumni_forum_thread_id' => AlumniForumThread::factory(),
            'alumni_account_id' => AlumniAccount::factory(),
            'parent_id' => null,
            'body' => fake()->paragraph(),
        ];
    }
}
