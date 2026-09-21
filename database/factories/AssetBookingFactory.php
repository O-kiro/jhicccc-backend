<?php

namespace Database\Factories;

use App\Models\AssetBooking;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetBooking>
 */
class AssetBookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'asset_id' => null,
            'borrower' => fake()->name(),
            'purpose' => fake()->sentence(5),
            'starts_at' => now()->addHours(2),
            'ends_at' => now()->addHours(5),
            'returned_at' => null,
        ];
    }
}
