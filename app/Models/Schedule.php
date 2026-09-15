<?php

namespace App\Models;

use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['classroom_id', 'subject_id', 'teacher_id', 'day_of_week', 'starts_at', 'ends_at', 'meeting_url'])]
class Schedule extends Model
{
    /** @use HasFactory<ScheduleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
        ];
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Apakah sesi ini sedang berlangsung sekarang — menyalakan badge
     * "LIVE NOW" dan tombol "Gabung Kelas" di portal siswa.
     */
    public function isLiveNow(): bool
    {
        $now = now();

        if ((int) $now->dayOfWeekIso !== $this->day_of_week) {
            return false;
        }

        $time = $now->format('H:i:s');

        return $time >= $this->starts_at && $time < $this->ends_at;
    }

    /**
     * @param  Builder<Schedule>  $query
     */
    public function scopeForToday(Builder $query): void
    {
        $query->where('day_of_week', now()->dayOfWeekIso);
    }
}
