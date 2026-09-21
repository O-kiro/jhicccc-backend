<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ticket', 'applicant_name', 'contact', 'service_id',
    'note', 'status', 'submitted_at', 'completed_at',
])]
class ServiceRequest extends Model
{
    use HasFactory;

    public const STATUS = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
