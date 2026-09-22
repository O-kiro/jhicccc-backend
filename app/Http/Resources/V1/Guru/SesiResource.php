<?php

namespace App\Http\Resources\V1\Guru;

use App\Models\Schedule;
use App\Support\Hari;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Satu sesi mengajar dari sisi guru: yang penting kelasnya, bukan gurunya.
 *
 * @mixin Schedule
 */
class SesiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day' => $this->day_of_week,
            'day_label' => Hari::nama($this->day_of_week),
            'subject' => $this->subject->name,
            'classroom' => $this->classroom->name,
            'start' => substr((string) $this->starts_at, 0, 5),
            'end' => substr((string) $this->ends_at, 0, 5),
            'meeting_url' => $this->meeting_url,
            'live' => $this->isLiveNow(),
        ];
    }
}
