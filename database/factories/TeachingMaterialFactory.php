<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\TeachingMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeachingMaterial>
 */
class TeachingMaterialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'subject_id' => null,
            'type' => 'lkpd',
            'title' => fake()->sentence(3),
            'level' => 'XI',
            'url' => null,
            'description' => null,
        ];
    }
}
