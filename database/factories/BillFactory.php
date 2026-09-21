<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\PaymentType;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bill>
 */
class BillFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nominal = fake()->numberBetween(50_000, 500_000);

        return [
            'student_id' => Student::factory(),
            'payment_type_id' => PaymentType::factory(),
            'period' => now()->format('Y-m'),
            'amount' => $nominal,
            'amount_paid' => 0,
            'due_on' => now()->addDays(14),
            'paid_at' => null,
            'receipt_no' => null,
        ];
    }

    public function lunas(): static
    {
        return $this->state(fn (array $atribut): array => [
            'amount_paid' => $atribut['amount'],
            'paid_at' => now(),
            'receipt_no' => 'KW-'.fake()->unique()->numerify('#####'),
        ]);
    }
}
