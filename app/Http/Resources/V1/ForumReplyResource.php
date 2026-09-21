<?php

namespace App\Http\Resources\V1;

use App\Models\ForumReply;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ForumReply
 */
class ForumReplyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author' => $this->student?->name ?? 'Anonim',
            'body' => $this->body,
            'when' => $this->created_at?->diffForHumans(),
            // Dipakai portal untuk menandai balasan milik siswa sendiri.
            'is_mine' => $this->student_id === $request->user()?->id,
        ];
    }
}
