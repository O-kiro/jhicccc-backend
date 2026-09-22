<?php

namespace App\Models;

use Database\Factories\BookLoanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

/**
 * Satu buku yang dipinjam satu orang. Peminjamnya siswa atau guru — tepat
 * salah satu dari student_id dan teacher_id terisi.
 */
#[Fillable(['book_id', 'student_id', 'teacher_id', 'due_on', 'returned_at', 'current_page'])]
class BookLoan extends Model
{
    /** @use HasFactory<BookLoanFactory> */
    use HasFactory;

    /**
     * Batas buku yang boleh dipinjam bersamaan oleh satu peminjam. Dipakai
     * portal untuk bilah kuota dan meja sirkulasi untuk menolak pinjaman.
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

    protected static function booted(): void
    {
        // Pengganti CHECK constraint yang tidak bisa ditambahkan di SQLite.
        static::saving(function (BookLoan $pinjaman): void {
            if (blank($pinjaman->student_id) === blank($pinjaman->teacher_id)) {
                throw new InvalidArgumentException('Pinjaman harus milik tepat satu siswa atau satu guru.');
            }
        });
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function peminjam(): Student|Teacher|null
    {
        return $this->student_id ? $this->student : $this->teacher;
    }

    /** Dipakai portal untuk menolak mengembalikan pinjaman orang lain. */
    public function milik(Student|Teacher $orang): bool
    {
        return $orang instanceof Teacher
            ? $this->teacher_id === $orang->id
            : $this->student_id === $orang->id;
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
