<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanGambarUnggahan;
use App\Models\Concerns\MemicuRevalidasiSitus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'title', 'category', 'published_on', 'author', 'excerpt', 'tone', 'content', 'image', 'image_path', 'is_published'])]
class NewsPost extends Model
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
            'published_on' => 'date',
            'content' => 'array',
            'is_published' => 'boolean',
        ];
    }

    /** Hanya yang sudah terbit dan tanggalnya tidak di masa depan. */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->whereDate('published_on', '<=', now());
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
