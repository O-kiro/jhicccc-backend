<?php

namespace App\Http\Resources\V1;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Exam
 */
class ExamResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $detik = (int) now()->diffInSeconds($this->starts_at, false);

        return [
            'id' => $this->id,
            'subject' => $this->subject->name,
            'title' => $this->title,
            'priority' => $this->priority,
            'when' => $this->starts_at->translatedFormat('j F Y, H:i').'–'.$this->ends_at->format('H:i'),
            // Dihitung server agar countdown di portal tidak bergantung pada
            // jam perangkat siswa. Null kalau ujiannya sudah dimulai.
            'starts_in_seconds' => $detik > 0 ? $detik : null,
            // Hanya saat is_live bernilai true /exam-session mau melayani
            // ujian ini; portal memakainya untuk menentukan kapan tombol
            // "Masuk Ke Dalam Ujian" boleh muncul.
            'is_live' => $detik <= 0 && $this->ends_at->isFuture(),
        ];
    }
}
