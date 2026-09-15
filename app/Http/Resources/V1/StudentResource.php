<?php

namespace App\Http\Resources\V1;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Student
 */
class StudentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nisn' => $this->nisn,
            'name' => $this->name,
            'kelas' => $this->classroom?->name,
            'academic_year' => $this->classroom?->academic_year,
            'streak_days' => $this->streak_days,
        ];
    }
}
