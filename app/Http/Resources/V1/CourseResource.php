<?php

namespace App\Http\Resources\V1;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Course
 *
 * Progres diambil dari relasi `enrollments` yang sudah dibatasi pada siswa
 * yang sedang masuk oleh CourseController.
 */
class CourseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->subject->name,
            'teacher' => $this->teacher?->name,
            'category' => $this->subject->category,
            'icon' => $this->subject->icon,
            'tone' => $this->subject->tone,
            // Dihitung dari relasi supaya angka di kartu tidak pernah
            // berbeda dari isi daftar yang dibuka siswa.
            'modules' => $this->modules->count(),
            'progress' => (int) ($this->enrollments->first()?->progress_percentage ?? 0),
            'module_list' => $this->modules->map(fn ($m): array => [
                'id' => $m->id,
                'number' => $m->number,
                'title' => $m->title,
                'description' => $m->description,
                // Null berarti materinya belum diunggah guru.
                'url' => $m->url,
            ])->all(),
        ];
    }
}
