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
        $dihapus = $this->trashed();

        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            // Balasan yang dihapus hanya muncul sebagai penanda bila masih ada
            // yang membalasnya; isinya tidak ikut dikirim.
            'is_deleted' => $dihapus,
            'author' => $dihapus ? null : ($this->student?->name ?? 'Anonim'),
            'body' => $dihapus ? null : $this->body,
            'when' => $this->created_at?->diffForHumans(),
            'is_mine' => ! $dihapus && $this->student_id === $request->user()?->id,
            'children' => $this->whenLoaded(
                'children',
                fn () => self::collection($this->children)->resolve($request),
                [],
            ),
        ];
    }
}
