<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanGambarUnggahan;
use App\Models\Concerns\MemicuRevalidasiSitus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'student', 'level', 'year', 'organizer', 'field', 'image', 'image_path', 'sort'])]
class Achievement extends Model
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
            'year' => 'integer',
        ];
    }

    /**
     * Foto unggahan didahulukan; `image` menunjuk berkas statis di /public.
     * Sama seperti NewsPost dan Facility.
     */
    public function publicImage(): ?string
    {
        return $this->image_path ? '/storage/'.ltrim($this->image_path, '/') : $this->image;
    }

    /** Urutan tampil di situs, diatur admin lewat kolom sort. */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }
}
