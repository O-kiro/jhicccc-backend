<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'description', 'target', 'requirements', 'kind', 'is_active'])]
class Service extends Model
{
    use HasFactory;

    public const JENIS = ['layanan' => 'Layanan PTSP', 'kunjungan' => 'Jenis Kunjungan'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function visits(): HasMany
    {
        return $this->hasMany(GuestVisit::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
