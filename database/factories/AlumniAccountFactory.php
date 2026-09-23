<?php

namespace Database\Factories;

use App\Models\AlumniAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumniAccount>
 */
class AlumniAccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'graduation_year' => fake()->numberBetween(2015, 2026),
            'occupation' => fake()->jobTitle(),
            'is_active' => true,
        ];
    }

    /** Belum diberi sandi portal oleh admin. */
    public function tanpaAksesPortal(): static
    {
        return $this->state(fn (): array => ['password' => null]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
