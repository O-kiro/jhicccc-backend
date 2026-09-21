<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'teacher_id', 'kind', 'left_at', 'returned_at', 'reason'])]
class StudentPermit extends Model
{
    use HasFactory;

    public const JENIS = [
        'keluar_kelas' => 'Keluar Kelas',
        'masuk_kelas' => 'Masuk Kelas',
        'keluar_sekolah' => 'Keluar Lingkungan Sekolah',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['left_at' => 'datetime', 'returned_at' => 'datetime'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /** Masih di luar — belum tercatat kembali. */
    public function scopeOutstanding(Builder $query): void
    {
        $query->whereNull('returned_at');
    }
}
