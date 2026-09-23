<?php

namespace App\Http\Resources\V1\Alumni;

use App\Models\AlumniForumThread;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AlumniForumThread
 */
class TopikResource extends JsonResource
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
            'author' => $this->author->name,
            // Angkatan ikut tampil: di forum alumni, tahun lulus adalah
            // konteks terpenting untuk menilai pengalaman yang dibagikan.
            'author_note' => $this->author->labelAngkatan(),
            'when' => $this->created_at?->diffForHumans(),
            'excerpt' => $this->body,
            'replies' => (int) ($this->replies_count ?? 0),
            'likes' => $this->like_count,
            'liked_by_me' => $this->relationLoaded('likes') ? $this->likes->isNotEmpty() : null,
        ];
    }
}
