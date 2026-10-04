<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanGambarUnggahan;
use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'title', 'author', 'description', 'url', 'category', 'total_pages', 'cover_path'])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    use MembersihkanGambarUnggahan;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['total_pages' => 'integer'];
    }

    protected function kolomBerkasUnggahan(): array
    {
        return ['cover_path'];
    }

    /** Sampul unggahan admin; null berarti portal menggambar sampul berwarna. */
    public function publicCover(): ?string
    {
        return $this->cover_path ? '/storage/'.ltrim($this->cover_path, '/') : null;
    }

    public function loans(): HasMany
    {
        return $this->hasMany(BookLoan::class);
    }
}
