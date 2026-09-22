<?php

namespace Tests\Feature;

use App\Filament\Pages\Modules\ELibrary;
use App\Filament\Resources\BookLoans\Pages\CreateBookLoan;
use App\Filament\Resources\Teachers\Pages\CreateTeacher;
use App\Filament\Resources\Teachers\Pages\EditTeacher;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Perpustakaan guru sama dengan perpustakaan siswa: endpoint, aturan
 * Sirkulasi, dan kuotanya satu. Yang berbeda hanya kolom peminjamnya.
 */
class GuruPerpustakaanTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_teacher_borrows_and_returns_through_the_shared_endpoints(): void
    {
        $guru = Teacher::factory()->create();
        $buku = Book::factory()->create();

        $this->actingAs($guru, 'teacher')
            ->postJson(route('api.v1.library.books.borrow', $buku), ['days' => 21])
            ->assertCreated()
            ->assertJsonPath('due_in_days', 21);

        $pinjaman = BookLoan::query()->sole();
        $this->assertSame($guru->id, $pinjaman->teacher_id);
        $this->assertNull($pinjaman->student_id);

        $this->actingAs($guru, 'teacher')
            ->getJson(route('api.v1.library'))
            ->assertJsonCount(1, 'loans');
        $this->actingAs($guru, 'teacher')
            ->getJson(route('api.v1.library.books'))
            ->assertJsonPath('books.0.borrowed_by_me', true);

        $this->actingAs($guru, 'teacher')
            ->postJson(route('api.v1.library.loans.return', $pinjaman))
            ->assertOk();
        $this->assertNotNull($pinjaman->refresh()->returned_at);
    }

    /**
     * ID siswa dan guru bisa sama; pinjaman tetap tidak tertukar karena
     * kepemilikan dinilai dari kolomnya, bukan dari angka ID saja.
     */
    public function test_loans_do_not_leak_between_a_student_and_a_teacher_with_the_same_id(): void
    {
        $siswa = Student::factory()->create();
        $guru = Teacher::factory()->create();
        $this->assertSame($siswa->id, $guru->id);

        $milikSiswa = BookLoan::factory()->for($siswa)->create();
        $milikGuru = BookLoan::factory()->untukGuru($guru)->create();

        $this->actingAs($guru, 'teacher')
            ->getJson(route('api.v1.library'))
            ->assertJsonCount(1, 'loans')
            ->assertJsonPath('loans.0.id', $milikGuru->id);

        $this->actingAs($guru, 'teacher')
            ->postJson(route('api.v1.library.loans.return', $milikSiswa))
            ->assertNotFound();

        $this->app['auth']->forgetGuards();
        $this->actingAs($siswa, 'student')
            ->postJson(route('api.v1.library.loans.return', $milikGuru))
            ->assertNotFound();

        $this->assertNull($milikSiswa->refresh()->returned_at);
        $this->assertNull($milikGuru->refresh()->returned_at);
    }

    public function test_the_quota_applies_to_teachers_too(): void
    {
        $guru = Teacher::factory()->create();
        BookLoan::factory()->count(BookLoan::KUOTA)->untukGuru($guru)->create();

        $this->actingAs($guru, 'teacher')
            ->postJson(route('api.v1.library.books.borrow', Book::factory()->create()))
            ->assertStatus(422);
    }

    public function test_a_loan_must_belong_to_exactly_one_borrower(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BookLoan::factory()->create(['teacher_id' => Teacher::factory()->create()->id]);
    }

    public function test_the_desk_serves_a_teacher_by_nip(): void
    {
        $this->actingAs(User::factory()->create());
        $guru = Teacher::factory()->create(['nip' => '198001012005011001', 'name' => 'Guru Meja']);
        Book::factory()->create(['code' => 'PUS-901', 'title' => 'Buku Guru']);

        $halaman = Livewire::test(ELibrary::class)
            ->set('nisn', '19800101 200501 1 001')
            ->call('cariSiswa')
            ->assertHasNoErrors()
            ->assertSee('Guru Meja')
            ->assertSee('NIP 198001012005011001')
            ->set('buku', 'PUS-901')
            ->call('pinjam')
            ->assertHasNoErrors();

        $pinjaman = BookLoan::query()->sole();
        $this->assertTrue($pinjaman->teacher->is($guru));

        $halaman->call('kembalikan', $pinjaman->id)->assertHasNoErrors();
        $this->assertNotNull($pinjaman->refresh()->returned_at);
    }

    public function test_the_desk_refuses_an_inactive_teacher(): void
    {
        $this->actingAs(User::factory()->create());
        Teacher::factory()->inactive()->create(['nip' => '198001012005011002']);

        Livewire::test(ELibrary::class)
            ->set('nisn', '198001012005011002')
            ->call('cariSiswa')
            ->assertHasErrors('nisn');
    }

    /** Diubah dari browser, peminjam yang dilayani tidak boleh berganti. */
    public function test_the_served_borrower_cannot_be_swapped_from_the_browser(): void
    {
        $this->actingAs(User::factory()->create());
        Student::factory()->create(['nisn' => '777']);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(ELibrary::class)
            ->set('nisn', '777')
            ->call('cariSiswa')
            ->set('peminjamId', 999);
    }

    public function test_the_admin_can_record_a_loan_for_a_teacher_but_not_for_nobody(): void
    {
        $this->actingAs(User::factory()->create());
        $buku = Book::factory()->create();
        $guru = Teacher::factory()->create();

        Livewire::test(CreateBookLoan::class)
            ->fillForm(['book_id' => $buku->id, 'student_id' => null, 'teacher_id' => null])
            ->call('create')
            ->assertHasFormErrors(['student_id']);

        Livewire::test(CreateBookLoan::class)
            ->fillForm(['book_id' => $buku->id, 'student_id' => Student::factory()->create()->id, 'teacher_id' => $guru->id])
            ->call('create')
            ->assertHasFormErrors(['student_id']);

        Livewire::test(CreateBookLoan::class)
            ->fillForm(['book_id' => $buku->id, 'teacher_id' => $guru->id, 'due_on' => now()->addWeek()->toDateString()])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame($guru->id, BookLoan::query()->sole()->teacher_id);
    }

    public function test_the_admin_gives_portal_access_and_a_blank_password_keeps_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateTeacher::class)
            ->fillForm(['name' => 'Guru Baru', 'email' => 'guru.baru@madrasah.test', 'password' => 'awal12345'])
            ->call('create')
            ->assertHasNoFormErrors();

        $guru = Teacher::query()->where('email', 'guru.baru@madrasah.test')->sole();
        $this->assertTrue(Hash::check('awal12345', $guru->password));

        Livewire::test(EditTeacher::class, ['record' => $guru->getRouteKey()])
            ->assertSchemaStateSet(['password' => null])
            ->fillForm(['name' => 'Guru Baru, S.Pd', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('awal12345', $guru->refresh()->password));
        $this->assertSame('Guru Baru, S.Pd', $guru->name);
    }
}
