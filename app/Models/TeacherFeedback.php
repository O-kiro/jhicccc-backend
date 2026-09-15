<?php

namespace App\Models;

use Database\Factories\TeacherFeedbackFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'teacher_id', 'role', 'body'])]
class TeacherFeedback extends Model
{
    /** @use HasFactory<TeacherFeedbackFactory> */
    use HasFactory;

    /**
     * Laravel memluralkan "TeacherFeedback" jadi "teacher_feedbacks";
     * tabelnya bernama tunggal.
     */
    protected $table = 'teacher_feedback';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
