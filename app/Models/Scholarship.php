<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'category', 'name', 'quota', 'benefits', 'target',
    'deadline', 'status', 'applicants', 'verified', 'url', 'sort',
])]
class Scholarship extends Model
{
    use HasFactory;

    /** Status beserta labelnya; dipakai badge portal dan pilihan di panel. */
    public const STATUS = [
        'dibuka' => 'Pendaftaran Dibuka',
        'segera_ditutup' => 'Segera Ditutup',
        'ditutup' => 'Ditutup',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'quota' => 'integer',
            'applicants' => 'integer',
            'verified' => 'integer',
        ];
    }

    /** Program yang masih menerima pendaftar. */
    public function scopeAktif(Builder $query): void
    {
        $query->whereIn('status', ['dibuka', 'segera_ditutup']);
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }
}
