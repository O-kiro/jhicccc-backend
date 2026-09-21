<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'title', 'category', 'published_on', 'author', 'excerpt', 'tone', 'content', 'image', 'is_published'])]
class NewsPost extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_on' => 'date',
            'content' => 'array',
            'is_published' => 'boolean',
        ];
    }

    /** Hanya yang sudah terbit dan tanggalnya tidak di masa depan. */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->whereDate('published_on', '<=', now());
    }
}
