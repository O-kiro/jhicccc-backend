<?php

namespace Database\Factories;

use App\Models\CashEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashEntry>
 */
class CashEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_date' => now()->subDays(fake()->numberBetween(0, 60)),
            'direction' => fake()->randomElement(['masuk', 'keluar']),
            'category' => fake()->randomElement(['Iuran Komite', 'Kegiatan', 'Operasional', 'Sarana']),
            'description' => fake()->sentence(5),
            'amount' => fake()->numberBetween(100_000, 5_000_000),
            'reference' => null,
        ];
    }
}
