<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket' => strtoupper(fake()->unique()->bothify('PTSP-#####')),
            'applicant_name' => fake()->name(),
            'contact' => fake()->numerify('08##########'),
            'service_id' => Service::factory(),
            'note' => fake()->sentence(8),
            'status' => 'baru',
            'submitted_at' => now()->subDays(fake()->numberBetween(0, 10)),
            'completed_at' => null,
        ];
    }
}
