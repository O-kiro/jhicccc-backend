<?php

namespace App\Models;

use Database\Factories\ReportCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id', 'academic_year', 'semester',
    'average_score', 'class_rank', 'class_size', 'attendance_percentage',
])]
class ReportCard extends Model
{
    /** @use HasFactory<ReportCardFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'average_score' => 'decimal:2',
            'attendance_percentage' => 'decimal:2',
            'class_rank' => 'integer',
            'class_size' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
