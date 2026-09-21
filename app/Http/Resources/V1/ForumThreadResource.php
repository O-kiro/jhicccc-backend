<?php

namespace App\Http\Resources\V1;

use App\Models\ForumThread;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ForumThread
 */
class ForumThreadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category->name,
            'title' => $this->title,
            'author' => $this->student->name,
            // Bukan bentuk singkat: "1hr" untuk "1 hari" gampang terbaca
            // sebagai "1 hour" di antarmuka yang campur Indonesia–Inggris.
            'when' => $this->created_at?->diffForHumans(),
            'excerpt' => $this->body,
            'replies' => (int) ($this->replies_count ?? 0),
            'likes' => $this->like_count,
            // Hanya dihitung kalau relasinya sudah dimuat, supaya daftar
            // diskusi tidak memicu satu kueri tambahan per baris.
            'liked_by_me' => $this->relationLoaded('likes')
                ? $this->likes->isNotEmpty()
                : null,
        ];
    }
}
