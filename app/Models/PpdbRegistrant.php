<?php

namespace App\Models;

use App\Models\Concerns\MencabutTokenSaatNonaktif;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Calon siswa yang mengunggah berkas PPDB. Masuk dengan nomor pendaftaran
 * pada guard "ppdb" — terpisah dari siswa, guru, dan alumni.
 */
#[Fillable([
    'registration_number', 'name', 'jalur', 'password',
    'phone', 'origin_school', 'is_active', 'note',
])]
#[Hidden(['password'])]
class PpdbRegistrant extends Authenticatable
{
    use HasApiTokens, HasFactory, MencabutTokenSaatNonaktif;

    /** Jalur pendaftaran beserta berkas wajibnya. */
    public const JALUR = ['Prestasi', 'Reguler 1', 'Reguler 2'];

    /**
     * Berkas yang diminta panitia. Kuncinya dipakai sebagai `jenis` di tabel
     * berkas, jadi jangan diubah setelah ada unggahan.
     *
     * @var array<string, array{label: string, wajib: list<string>}>
     */
    public const BERKAS = [
        'ijazah' => ['label' => 'Fotokopi Ijazah atau SKL (SMP / MTs)', 'wajib' => self::JALUR],
        'rapor' => ['label' => 'Fotokopi Rapor Semester 1–5', 'wajib' => self::JALUR],
        'kartu_keluarga' => ['label' => 'Fotokopi Kartu Keluarga', 'wajib' => self::JALUR],
        'akta' => ['label' => 'Fotokopi Akta Kelahiran', 'wajib' => self::JALUR],
        'foto' => ['label' => 'Pas Foto', 'wajib' => self::JALUR],
        // Hanya jalur Prestasi yang wajib melampirkannya; jalur lain boleh.
        'sertifikat' => ['label' => 'Sertifikat Prestasi', 'wajib' => ['Prestasi']],
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean'];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PpdbDocument::class);
    }

    public function bisaMasukPortal(): bool
    {
        return filled($this->password);
    }

    /** Berkas wajib untuk jalur pendaftar ini. */
    public function jenisWajib(): array
    {
        return array_keys(array_filter(
            self::BERKAS,
            fn (array $b): bool => in_array($this->jalur, $b['wajib'], true),
        ));
    }
}
