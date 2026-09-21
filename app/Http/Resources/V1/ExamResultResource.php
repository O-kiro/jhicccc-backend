<?php

namespace App\Http\Resources\V1;

use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ExamResult
 */
class ExamResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->exam->subject->name,
            'title' => $this->exam->title,
            'score' => $this->score,
            'finished_on' => $this->finished_at?->toDateString(),
        ];
    }
}
