<?php

namespace App\Http\Resources\V1;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Teacher
 */
class TeacherResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nip' => $this->nip,
            'email' => $this->email,
            // Nama kelas perwalian; null bila bukan wali kelas.
            'homeroom' => $this->homeroomClassrooms->pluck('name')->join(', ') ?: null,
        ];
    }
}
