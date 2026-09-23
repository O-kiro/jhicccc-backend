<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['alumni_forum_thread_id', 'alumni_account_id', 'parent_id', 'body'])]
class AlumniForumReply extends Model
{
    use HasFactory, SoftDeletes;

    public function thread(): BelongsTo
    {
        return $this->belongsTo(AlumniForumThread::class, 'alumni_forum_thread_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(AlumniAccount::class, 'alumni_account_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
