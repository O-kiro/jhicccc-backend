<?php

namespace App\Models;

use App\Models\Concerns\MencabutTokenSaatNonaktif;
use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Guru. Masuk ke Portal Guru dengan email atau NIP, memakai token Sanctum
 * pada guard "teacher" — terpisah dari guard siswa, jadi token guru ditolak
 * di endpoint khusus siswa dan sebaliknya.
 */
#[Fillable(['name', 'nip', 'email', 'password', 'is_active'])]
#[Hidden(['password'])]
class Teacher extends Authenticatable
{
    /** @use HasFactory<TeacherFactory> */
    use HasApiTokens, HasFactory, MencabutTokenSaatNonaktif;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /** Guru tanpa sandi belum diberi akses portal. */
    public function bisaMasukPortal(): bool
    {
        return filled($this->password);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(TeachingJournal::class);
    }

    /** Kelas yang diwalikan. Satu guru lazimnya satu kelas, tapi tidak dipaksa. */
    public function homeroomClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
    }

    public function bookLoans(): HasMany
    {
        return $this->hasMany(BookLoan::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(TeacherFeedback::class);
    }
}
