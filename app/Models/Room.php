<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'type', 'capacity', 'condition', 'note'])]
class Room extends Model
{
    use HasFactory;

    public const KONDISI = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['capacity' => 'integer'];
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(AssetBooking::class);
    }
}
