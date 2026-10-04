<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Tugas dari guru untuk satu kursus (mapel di satu kelas). */
#[Fillable(['course_id', 'title', 'description', 'url', 'due_at'])]
class Task extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['due_at' => 'datetime'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    /** Tugas untuk kelas siswa ini. */
    public function scopeUntukSiswa(Builder $query, Student $student): void
    {
        $query->whereHas('course', fn (Builder $q) => $q->where('classroom_id', $student->classroom_id));
    }

    /** Yang belum ditandai selesai oleh siswa ini. */
    public function scopeBelumSelesai(Builder $query, Student $student): void
    {
        $query->whereDoesntHave('completions', fn (Builder $q) => $q->where('student_id', $student->id));
    }
}
