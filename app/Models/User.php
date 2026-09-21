<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Peran;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Hanya akun berperan sah yang boleh masuk panel.
     *
     * Tanpa kontrak FilamentUser, Filament mengizinkan semua akun selama
     * APP_ENV=local — tapi menolak SEMUA akun di produksi. Artinya sebelum
     * ini, memasang situs dengan APP_ENV=production mengunci semua admin.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return Peran::sah($this->role);
    }

    /**
     * Pengaman terakhir di tingkat model — berlaku juga di luar panel
     * (tinker, seeder): Admin Utama terakhir tidak boleh hilang, kalau tidak
     * tidak ada lagi yang bisa mengelola pengguna.
     */
    protected static function booted(): void
    {
        // getOriginal(), bukan $user->role: setelah penurunan peran ditolak,
        // nilai di memori sudah terlanjur berubah walau basis datanya tidak —
        // dan memeriksa nilai di memori sempat meloloskan penghapusan Admin
        // Utama terakhir.
        static::deleting(function (User $user): void {
            if ($user->getOriginal('role') === Peran::SUPER_ADMIN && self::jumlahAdminUtama() <= 1) {
                throw new \RuntimeException('Admin Utama terakhir tidak bisa dihapus.');
            }
        });

        static::updating(function (User $user): void {
            if ($user->getOriginal('role') === Peran::SUPER_ADMIN
                && $user->role !== Peran::SUPER_ADMIN
                && self::jumlahAdminUtama() <= 1) {
                throw new \RuntimeException('Admin Utama terakhir tidak bisa diturunkan perannya.');
            }
        });
    }

    private static function jumlahAdminUtama(): int
    {
        return self::query()->where('role', Peran::SUPER_ADMIN)->count();
    }

    public function labelPeran(): string
    {
        return Peran::LABEL[$this->role] ?? 'Tanpa peran';
    }
}
