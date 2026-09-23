<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['alumni_forum_category_id', 'alumni_account_id', 'title', 'body', 'like_count'])]
class AlumniForumThread extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['like_count' => 'integer'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AlumniForumCategory::class, 'alumni_forum_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(AlumniAccount::class, 'alumni_account_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(AlumniForumReply::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(AlumniForumThreadLike::class);
    }
}
