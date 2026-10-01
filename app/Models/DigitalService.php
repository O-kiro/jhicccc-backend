<?php

namespace App\Models;

use App\Models\Concerns\MemicuRevalidasiSitus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'name', 'description', 'href', 'icon', 'tone', 'login_href', 'login_label', 'login_note', 'guide', 'full_name', 'audience', 'intro', 'about', 'highlights', 'features_title', 'features', 'steps', 'note', 'help', 'sort', 'is_active'])]
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
            'highlights' => 'array',
            'features' => 'array',
            'steps' => 'array',
            'guide' => 'array',
            'help' => 'array',
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
