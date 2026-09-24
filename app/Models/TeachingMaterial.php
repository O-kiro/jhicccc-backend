<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** LKPD dan bahan ajar; isinya ditautkan ke Drive atau situs lain. */
#[Fillable(['teacher_id', 'subject_id', 'type', 'title', 'level', 'url', 'description'])]
class TeachingMaterial extends Model
{
    use HasFactory;

    public const JENIS = ['lkpd' => 'LKPD', 'bahan_ajar' => 'Bahan Ajar'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function labelJenis(): string
    {
        return self::JENIS[$this->type] ?? $this->type;
    }
}
