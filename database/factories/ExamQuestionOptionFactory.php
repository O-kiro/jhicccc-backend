<?php

namespace Database\Factories;

use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamQuestionOption>
 */
class ExamQuestionOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_question_id' => ExamQuestion::factory(),
            'key' => 'A',
            'body' => fake()->words(4, true),
            'is_correct' => false,
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (): array => ['is_correct' => true]);
    }
}
