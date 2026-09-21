<?php

namespace App\Models;

use App\Models\Concerns\MemicuRevalidasiSitus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'name', 'description', 'href', 'icon', 'tone', 'login_href', 'login_label', 'full_name', 'audience', 'about', 'features', 'steps', 'note', 'sort', 'is_active'])]
class DigitalService extends Model
{
    use HasFactory;
    use MemicuRevalidasiSitus;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'about' => 'array',
            'features' => 'array',
            'steps' => 'array',
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
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
