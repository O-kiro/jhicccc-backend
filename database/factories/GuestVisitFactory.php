<?php

namespace Database\Factories;

use App\Models\GuestVisit;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestVisit>
 */
class GuestVisitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_code' => strtoupper(fake()->unique()->bothify('BT-#####')),
            'guest_name' => fake()->name(),
            'institution' => fake()->company(),
            'phone' => fake()->numerify('08##########'),
            'service_id' => Service::factory()->kunjungan(),
            'purpose' => fake()->sentence(6),
            'arrived_at' => now()->subHours(fake()->numberBetween(0, 6)),
            'finished_at' => null,
            'status' => 'pending',
            'rating' => null,
        ];
    }
}
