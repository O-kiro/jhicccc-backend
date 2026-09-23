<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['alumni_forum_thread_id', 'alumni_account_id'])]
class AlumniForumThreadLike extends Model
{
    public function thread(): BelongsTo
    {
        return $this->belongsTo(AlumniForumThread::class, 'alumni_forum_thread_id');
    }
}
