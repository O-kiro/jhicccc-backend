<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'discipline_rule_id', 'teacher_id', 'occurred_on', 'points', 'note'])]
class DisciplineRecord extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['occurred_on' => 'date', 'points' => 'integer'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(DisciplineRule::class, 'discipline_rule_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
