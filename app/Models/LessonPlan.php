<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Modul ajar milik guru. */
#[Fillable(['teacher_id', 'subject_id', 'classroom_id', 'title', 'time_range', 'url', 'note', 'status'])]
class LessonPlan extends Model
{
    use HasFactory;

    public const STATUS = ['aktif' => 'Aktif', 'arsip' => 'Arsip'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
