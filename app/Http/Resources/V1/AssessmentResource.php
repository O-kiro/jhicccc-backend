<?php

namespace App\Http\Resources\V1;

use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Assessment
 */
class AssessmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject->name,
            'title' => $this->title,
            'score' => $this->score,
            'assessed_on' => $this->assessed_on->toDateString(),
        ];
    }
}
