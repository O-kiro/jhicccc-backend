<?php

namespace App\Http\Resources\V1\Alumni;

use App\Models\AlumniForumCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AlumniForumCategory
 */
class KategoriResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'desc' => $this->description,
            'icon' => $this->icon,
            'tone' => $this->tone,
            // threads_count diisi withCount() di ForumController.
            'threads' => (int) ($this->threads_count ?? 0),
        ];
    }
}
