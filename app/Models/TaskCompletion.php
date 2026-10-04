<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['task_id', 'student_id', 'completed_at'])]
class TaskCompletion extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }
}
