<?php

namespace App\Models;

use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['subject_id', 'classroom_id', 'title', 'priority', 'starts_at', 'ends_at'])]
class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('number');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    /** Ujian yang belum selesai — dipakai daftar "Ujian Mendatang". */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('ends_at', '>=', now());
    }
}
