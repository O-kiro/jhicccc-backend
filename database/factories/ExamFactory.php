<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classroom_id' => Classroom::factory(),
            'subject_id' => Subject::factory(),
            'title' => Str::title(fake()->words(3, true)),
            'priority' => null,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(90),
            'question_count' => 40,
        ];
    }

    /** Sedang berlangsung: hanya keadaan ini yang dilayani /exam-session. */
    public function live(): static
    {
        return $this->state(fn (): array => [
            'starts_at' => now()->subMinutes(10),
            'ends_at' => now()->addMinutes(50),
        ]);
    }

    /** Sudah lewat — tidak boleh muncul sebagai ujian mendatang. */
    public function finished(): static
    {
        return $this->state(fn (): array => [
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDays(2)->addMinutes(90),
        ]);
    }
}
