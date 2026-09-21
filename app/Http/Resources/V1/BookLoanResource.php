<?php

namespace App\Http\Resources\V1;

use App\Models\BookLoan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BookLoan
 */
class BookLoanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $total = $this->book->total_pages;

        return [
            'id' => $this->id,
            'title' => $this->book->title,
            'author' => $this->book->author,
            'description' => $this->book->description,
            'url' => $this->book->url,
            // Label kartu "Lanjutkan Membaca" diturunkan dari progres, bukan
            // disimpan: sebuah buku yang belum dibuka memang belum dibaca.
            'badge' => $this->current_page > 0 ? 'Sedang dibaca' : 'Baru dipinjam',
            'due_in_days' => $this->daysUntilDue(),
            'current_page' => $this->current_page,
            'total_pages' => $total,
            'progress' => $total > 0 ? (int) round($this->current_page / $total * 100) : 0,
        ];
    }
}
