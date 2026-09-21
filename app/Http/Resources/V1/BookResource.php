<?php

namespace App\Http\Resources\V1;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Book
 */
class BookResource extends JsonResource
{
    /**
     * Ikon dan warna per kategori. Murni tampilan, jadi tidak disimpan di
     * basis data — tapi tetap satu sumber, dipakai juga oleh LibraryController
     * saat menyusun daftar kategori.
     *
     * @var array<string, array{icon: string, tone: string}>
     */
    public const TONES = [
        'Studi Islam' => ['icon' => 'tahfidz', 'tone' => 'teal'],
        'Sains & Teknologi' => ['icon' => 'flask', 'tone' => 'blue'],
        'Humaniora' => ['icon' => 'globe', 'tone' => 'gold'],
    ];

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'url' => $this->url,
            'category' => $this->category,
            'tone' => self::TONES[$this->category]['tone'] ?? 'teal',
            'total_pages' => $this->total_pages,
        ];
    }
}
