<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['area', 'title', 'description', 'file_url', 'year', 'status'])]
class IntegrityDocument extends Model
{
    use HasFactory;

    /** Enam area perubahan Zona Integritas. */
    public const AREA = [
        'Manajemen Perubahan',
        'Penataan Tatalaksana',
        'Penataan Sistem Manajemen SDM',
        'Penguatan Akuntabilitas',
        'Penguatan Pengawasan',
        'Peningkatan Kualitas Pelayanan Publik',
    ];

    public const STATUS = ['draf' => 'Draf', 'terkumpul' => 'Terkumpul', 'terverifikasi' => 'Terverifikasi'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['year' => 'integer'];
    }
}
