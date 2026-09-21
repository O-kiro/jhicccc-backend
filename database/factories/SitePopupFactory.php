<?php

namespace Database\Factories;

use App\Models\SitePopup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SitePopup>
 */
class SitePopupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'body' => fake()->sentence(12),
            'link_url' => null,
            'link_label' => null,
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => true,
        ];
    }
}
