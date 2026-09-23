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
 * Akun Portal Alumni. Masuk dengan surel pada guard "alumni" — terpisah dari
 * guard siswa dan guru, jadi tokennya tidak berlaku di portal mereka.
 */
#[Fillable(['name', 'email', 'password', 'graduation_year', 'occupation', 'is_active'])]
#[Hidden(['password'])]
class AlumniAccount extends Authenticatable
{
    use HasApiTokens, HasFactory, MencabutTokenSaatNonaktif;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'graduation_year' => 'integer',
        ];
    }

    /** Alumni tanpa sandi belum diberi akses portal. */
    public function bisaMasukPortal(): bool
    {
        return filled($this->password);
    }

    /** "Alumni 2019" — dipakai chip profil dan penulis topik forum. */
    public function labelAngkatan(): string
    {
        return 'Alumni '.$this->graduation_year;
    }

    public function forumThreads(): HasMany
    {
        return $this->hasMany(AlumniForumThread::class);
    }

    public function forumReplies(): HasMany
    {
        return $this->hasMany(AlumniForumReply::class);
    }
}
