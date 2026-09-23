<?php

namespace App\Http\Resources\V1\Alumni;

use App\Models\AlumniForumReply;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Balasan forum alumni. Balasan yang dihapus penulisnya tetap tampil sebagai
 * penanda bila masih punya anak, tapi isinya dikosongkan.
 *
 * @mixin AlumniForumReply
 */
class BalasanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dihapus = $this->trashed();
        $saya = $request->user();

        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'is_deleted' => $dihapus,
            'author' => $dihapus ? null : $this->author->name,
            'author_note' => $dihapus ? null : $this->author->labelAngkatan(),
            'body' => $dihapus ? null : $this->body,
            'when' => $dihapus ? null : $this->created_at?->diffForHumans(),
            'is_mine' => ! $dihapus && $this->alumni_account_id === $saya?->id,
            'children' => $this->relationLoaded('children')
                ? self::collection($this->children)->resolve($request)
                : [],
        ];
    }
}
