<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code', 'name', 'category', 'room_id', 'quantity', 'acquired_on', 'price', 'condition', 'note'])]
class Asset extends Model
{
    use HasFactory;

    public const KONDISI = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['quantity' => 'integer', 'price' => 'integer', 'acquired_on' => 'date'];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
