<?php

namespace App\Models;

use Database\Factories\ExamQuestionOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['exam_question_id', 'key', 'body', 'is_correct'])]
// Lapis pengaman kedua: kunci jawaban tidak ikut saat model diubah ke array
// atau JSON, sekalipun ada kode yang lupa memakai resource.
#[Hidden(['is_correct'])]
class ExamQuestionOption extends Model
{
    /** @use HasFactory<ExamQuestionOptionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }
}
