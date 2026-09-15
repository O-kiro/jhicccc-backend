<?php

namespace App\Http\Resources\V1;

use App\Models\ReportCard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReportCard
 */
class ReportCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year' => $this->academic_year,
            'semester' => $this->semester,
            'average_score' => (float) $this->average_score,
            'class_rank' => $this->class_rank,
            'class_size' => $this->class_size,
            'attendance_percentage' => (float) $this->attendance_percentage,
        ];
    }
}
