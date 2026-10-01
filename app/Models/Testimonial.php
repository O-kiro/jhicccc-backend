<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanGambarUnggahan;
use App\Models\Concerns\MemicuRevalidasiSitus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'role', 'quote', 'photo', 'photo_path', 'sort'])]
class Testimonial extends Model
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
        ];
    }

    /** Kolom unggahannya `photo_path`, bukan `image_path` bawaan trait. */
    protected function kolomBerkasUnggahan(): array
    {
        return ['photo_path'];
    }

    /** Foto unggahan didahulukan; `photo` menunjuk berkas statis di /public. */
    public function publicPhoto(): ?string
    {
        return $this->photo_path ? '/storage/'.ltrim($this->photo_path, '/') : $this->photo;
    }

    /** Urutan tampil di situs, diatur admin lewat kolom sort. */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }
}
