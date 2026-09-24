<?php

namespace Tests\Feature;

use App\Filament\Resources\AlumniAccounts\Pages\ListAlumniAccounts;
use App\Filament\Resources\PpdbRegistrants\Pages\ListPpdbRegistrants;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Teachers\Pages\ListTeachers;
use App\Models\AlumniAccount;
use App\Models\PpdbRegistrant;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pengganti alur "lupa kata sandi": madrasah belum punya server surel, jadi
 * admin yang menerbitkan sandi sementara.
 */
class AdminSetelUlangSandiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    /** @return array<string, array{class-string, class-string}> */
    public static function akun(): array
    {
        return [
            'siswa' => [ListStudents::class, Student::class],
            'guru' => [ListTeachers::class, Teacher::class],
            'alumni' => [ListAlumniAccounts::class, AlumniAccount::class],
            'pendaftar PPDB' => [ListPpdbRegistrants::class, PpdbRegistrant::class],
        ];
    }

    /**
     * @param  class-string  $halaman
     * @param  class-string<Model>  $model
     */
    #[DataProvider('akun')]
    public function test_the_admin_issues_a_new_password_and_ends_old_sessions(string $halaman, string $model): void
    {
        /** @var Model $akun */
        $akun = $model::factory()->create(['password' => 'lama12345']);
        $akun->createToken('perangkat-lama');

        Livewire::test($halaman)->callTableAction('setel_ulang_sandi', $akun);

        $akun->refresh();

        // Sandi lama tidak berlaku lagi dan sesi lama dicabut.
        $this->assertFalse(Hash::check('lama12345', $akun->password));
        $this->assertSame(0, $akun->tokens()->count());
    }
}
