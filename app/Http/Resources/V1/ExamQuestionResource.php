<?php

namespace App\Http\Resources\V1;

use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ExamQuestion
 *
 * PENTING: `is_correct` tidak pernah disertakan. Kunci jawaban tidak boleh
 * sampai ke portal siswa — siapa pun bisa membacanya dari respons jaringan.
 */
class ExamQuestionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Dipakai klien saat menyimpan jawaban; nomor urut tidak cukup
            // karena bisa sama antar ujian.
            'id' => $this->id,
            'number' => $this->number,
            'type' => $this->type,
            'body' => $this->body,
            'options' => $this->options->map(fn ($o): array => [
                'key' => $o->key,
                'text' => $o->body,
            ])->all(),
        ];
    }
}
