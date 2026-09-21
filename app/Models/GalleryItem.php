<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanGambarUnggahan;
use App\Models\Concerns\MemicuRevalidasiSitus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'date', 'category', 'tone', 'image', 'image_path', 'sort'])]
class GalleryItem extends Model
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
            'date' => 'date',
        ];
    }

    /** Urutan tampil di situs, diatur admin lewat kolom sort. */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort')->orderBy('id');
    }

    /**
     * Alamat gambar sebagaimana dipakai situs: unggahan lebih dulu, lalu
     * jalur lama. Unggahan dikembalikan sebagai /storage/..., yang diteruskan
     * Next.js ke backend — lihat rewrites di next.config.ts.
     */
    public function publicImage(): ?string
    {
        return $this->image_path ? '/storage/'.ltrim($this->image_path, '/') : $this->image;
    }
}
