<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanGambarUnggahan;
use App\Models\Concerns\MemicuRevalidasiSitus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'logo', 'logo_path', 'url', 'sort', 'is_active'])]
class Sponsor extends Model
{
    use HasFactory;
    use MembersihkanGambarUnggahan;
    use MemicuRevalidasiSitus;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** Kolom unggahannya `logo_path`, bukan `image_path` bawaan trait. */
    protected function kolomBerkasUnggahan(): array
    {
        return ['logo_path'];
    }

    /** Logo unggahan didahulukan; `logo` menunjuk berkas statis di /public. */
    public function publicLogo(): ?string
    {
        return $this->logo_path ? '/storage/'.ltrim($this->logo_path, '/') : $this->logo;
    }

    /** Urutan tampil di situs, diatur admin lewat kolom sort. */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
