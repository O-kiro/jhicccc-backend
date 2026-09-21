<?php

namespace App\Models;

use Database\Factories\ForumThreadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['forum_category_id', 'student_id', 'title', 'body', 'like_count'])]
class ForumThread extends Model
{
    /** @use HasFactory<ForumThreadFactory> */
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
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ForumReply::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ForumThreadLike::class);
    }

    /** Apakah siswa ini sudah menyukai topiknya. */
    public function likedBy(Student $student): bool
    {
        return $this->likes()->where('student_id', $student->id)->exists();
    }
}
