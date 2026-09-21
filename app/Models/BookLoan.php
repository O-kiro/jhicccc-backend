<?php

namespace App\Models;

use Database\Factories\BookLoanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['book_id', 'student_id', 'due_on', 'returned_at', 'current_page'])]
class BookLoan extends Model
{
    /** @use HasFactory<BookLoanFactory> */
    use HasFactory;

    /**
     * Batas buku yang boleh dipinjam bersamaan oleh satu siswa. Dipakai portal
     * siswa untuk bilah kuota dan meja sirkulasi untuk menolak pinjaman.
     */
    public const KUOTA = 5;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_on' => 'date',
            'returned_at' => 'datetime',
            'current_page' => 'integer',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** Pinjaman yang belum dikembalikan. */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('returned_at');
    }

    /** Sisa hari menuju jatuh tempo; negatif berarti sudah lewat. */
    public function daysUntilDue(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->due_on->startOfDay(), false);
    }
}
