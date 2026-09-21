<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['room_id', 'asset_id', 'borrower', 'purpose', 'starts_at', 'ends_at', 'returned_at'])]
class AssetBooking extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'returned_at' => 'datetime'];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** Nama apa pun yang dipinjam — ruang atau barang. */
    public function subjectName(): string
    {
        return $this->room?->name ?? $this->asset?->name ?? '—';
    }
}
