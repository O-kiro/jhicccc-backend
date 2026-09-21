<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'registration_code', 'guest_name', 'institution', 'phone',
    'service_id', 'purpose', 'arrived_at', 'finished_at', 'status', 'rating',
])]
class GuestVisit extends Model
{
    use HasFactory;

    public const STATUS = ['pending' => 'Pending', 'kunjungan' => 'Kunjungan', 'selesai' => 'Selesai'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'arrived_at' => 'datetime',
            'finished_at' => 'datetime',
            'rating' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
