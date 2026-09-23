<?php

namespace Database\Factories;

use App\Models\AlumniOutcome;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumniOutcome>
 */
class AlumniOutcomeFactory extends Factory
{
    /**
     * Kategori digilir, bukan diacak: pasangan (tahun, kategori) unik, dan
     * dua baris acak gampang bertabrakan.
     */
    private static int $urutan = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kategori = array_keys(AlumniOutcome::KATEGORI);

        return [
            'year' => 2026,
            'category' => $kategori[self::$urutan++ % count($kategori)],
            'students' => fake()->numberBetween(10, 200),
            'note' => fake()->sentence(3),
            'sort' => 0,
        ];
    }
}
