<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['body', 'source', 'is_active'])]
class Quote extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @param  Builder<Quote>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Kutipan sapaan hari ini; null bila admin menonaktifkan semuanya.
     *
     * Berganti tiap hari, tapi sama bagi semua orang pada hari yang sama:
     * dipilih dari urutan tetap, bukan acak, supaya memuat ulang halaman tidak
     * mengganti kutipan di tengah hari.
     */
    public static function hariIni(): ?self
    {
        $semua = static::query()->active()->orderBy('id')->get();

        return $semua->isEmpty() ? null : $semua[(int) now()->dayOfYear % $semua->count()];
    }
}
