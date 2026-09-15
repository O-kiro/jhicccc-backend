<?php

namespace App\Models;

use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'nip', 'email'])]
class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory;

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(TeacherFeedback::class);
    }
}
