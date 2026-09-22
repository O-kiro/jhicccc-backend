<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookLoan>
 */
class BookLoanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'student_id' => Student::factory(),
            'due_on' => now()->addDays(fake()->numberBetween(1, 14)),
            'returned_at' => null,
            'current_page' => 0,
        ];
    }

    /** Dipinjam guru. Kolom siswa dikosongkan: peminjam hanya boleh satu. */
    public function untukGuru(?Teacher $guru = null): static
    {
        return $this->state(fn (): array => [
            'student_id' => null,
            'teacher_id' => $guru?->id ?? Teacher::factory(),
        ]);
    }

    /** Pinjaman yang sudah dikembalikan — tidak boleh muncul di portal. */
    public function returned(): static
    {
        return $this->state(fn (): array => ['returned_at' => now()->subDay()]);
    }
}
