<?php

namespace Database\Factories;

use App\Models\IntegrityDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntegrityDocument>
 */
class IntegrityDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'area' => fake()->randomElement(IntegrityDocument::AREA),
            'title' => fake()->sentence(5),
            'description' => fake()->sentence(10),
            'file_url' => null,
            'year' => (int) now()->format('Y'),
            'status' => 'draf',
        ];
    }
}
