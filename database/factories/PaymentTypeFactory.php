<?php

namespace Database\Factories;

use App\Models\PaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentType>
 */
class PaymentTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('BYR-###')),
            'name' => fake()->randomElement(['SPP', 'Seragam', 'Kegiatan', 'Ujian']).' '.fake()->year(),
            'amount' => fake()->numberBetween(50_000, 500_000),
            'period' => 'bulanan',
            'is_active' => true,
        ];
    }
}
