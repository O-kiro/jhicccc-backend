<?php

namespace App\Http\Resources\V1;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Schedule
 */
class ScheduleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject->name,
            'teacher' => $this->teacher->name,
            'start' => substr((string) $this->starts_at, 0, 5),
            'end' => substr((string) $this->ends_at, 0, 5),
            'meeting_url' => $this->meeting_url,
            'live' => $this->isLiveNow(),
        ];
    }
}
