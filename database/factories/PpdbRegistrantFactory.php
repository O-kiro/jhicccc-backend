<?php

namespace Database\Factories;

use App\Models\PpdbRegistrant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PpdbRegistrant>
 */
class PpdbRegistrantFactory extends Factory
{
    /** Nomor pendaftaran digilir supaya tidak bertabrakan. */
    private static int $urutan = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_number' => sprintf('PPDB26-%04d', ++self::$urutan),
            'name' => fake()->name(),
            'jalur' => PpdbRegistrant::JALUR[0],
            'password' => 'password',
            'phone' => fake()->numerify('08##########'),
            'origin_school' => 'SMPN '.fake()->numberBetween(1, 10).' Kota Batu',
            'is_active' => true,
            'note' => null,
        ];
    }

    public function tanpaAksesPortal(): static
    {
        return $this->state(fn (): array => ['password' => null]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
