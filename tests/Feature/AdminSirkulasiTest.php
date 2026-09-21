<?php

namespace Tests\Feature;

use App\Filament\Pages\Modules\ELibrary;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use App\Models\User;
use App\Services\Sirkulasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSirkulasiTest extends TestCase
{
    use RefreshDatabase;

    private Sirkulasi $sirkulasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->sirkulasi = new Sirkulasi;
    }

    /** Pembaca kartu sering menambahkan spasi atau baris baru di ujung. */
    public function test_a_scanned_nisn_with_trailing_whitespace_is_found(): void
    {
        $siswa = Student::factory()->create(['nisn' => '0012345678']);

        $this->assertTrue($this->sirkulasi->siswa("0012345678\n")->is($siswa));
    }

    public function test_an_inactive_student_cannot_borrow(): void
    {
        Student::factory()->create(['nisn' => '555', 'is_active' => false]);

        $this->expectException(ValidationException::class);
        $this->sirkulasi->siswa('555');
    }

    public function test_a_book_is_found_by_its_scanned_code(): void
    {
        $buku = Book::factory()->create(['code' => 'PUS-042']);

        $this->assertTrue($this->sirkulasi->buku('PUS-042')->is($buku));
    }

    public function test_borrowing_creates_a_loan_with_the_chosen_due_date(): void
    {
        $siswa = Student::factory()->create();
        $buku = Book::factory()->create();

        $pinjaman = $this->sirkulasi->pinjam($siswa, $buku, 7);

        $this->assertSame(now()->addDays(7)->toDateString(), $pinjaman->due_on->toDateString());
        $this->assertNull($pinjaman->returned_at);
    }

    public function test_the_same_student_cannot_borrow_the_same_book_twice(): void
    {
        $siswa = Student::factory()->create();
        $buku = Book::factory()->create();
        $this->sirkulasi->pinjam($siswa, $buku, 14);

        $this->expectException(ValidationException::class);
        $this->sirkulasi->pinjam($siswa, $buku, 14);
    }

    public function test_the_loan_quota_is_enforced(): void
    {
        $siswa = Student::factory()->create();
        BookLoan::factory()->count(BookLoan::KUOTA)->for($siswa)->create();

        $this->expectException(ValidationException::class);
        $this->sirkulasi->pinjam($siswa, Book::factory()->create(), 14);
    }

    /** Buku yang sudah dikembalikan tidak lagi menghitung ke kuota. */
    public function test_returned_books_free_up_the_quota(): void
    {
        $siswa = Student::factory()->create();
        BookLoan::factory()->count(BookLoan::KUOTA)->for($siswa)->returned()->create();

        $pinjaman = $this->sirkulasi->pinjam($siswa, Book::factory()->create(), 14);

        $this->assertNotNull($pinjaman->id);
    }

    public function test_the_desk_page_runs_the_full_flow(): void
    {
        $siswa = Student::factory()->create(['nisn' => '777', 'name' => 'Siswa Meja']);
        $buku = Book::factory()->create(['code' => 'PUS-900', 'title' => 'Buku Meja']);

        $halaman = Livewire::test(ELibrary::class)
            ->set('nisn', '777')
            ->call('cariSiswa')
            ->assertHasNoErrors()
            ->assertSee('Siswa Meja')
            ->set('buku', 'PUS-900')
            ->set('durasi', 7)
            ->call('pinjam')
            ->assertHasNoErrors()
            ->assertSee('Buku Meja');

        $pinjaman = BookLoan::query()->sole();
        $this->assertTrue($pinjaman->student->is($siswa));

        $halaman->call('kembalikan', $pinjaman->id)->assertHasNoErrors();
        $this->assertNotNull($pinjaman->refresh()->returned_at);
    }

    public function test_an_unknown_nisn_shows_an_error_on_the_desk(): void
    {
        Livewire::test(ELibrary::class)
            ->set('nisn', '000')
            ->call('cariSiswa')
            ->assertHasErrors('nisn');
    }

    /** Meja tidak boleh mengembalikan pinjaman milik siswa lain. */
    public function test_the_desk_cannot_return_another_students_loan(): void
    {
        $dilayani = Student::factory()->create(['nisn' => '111']);
        $lain = BookLoan::factory()->create();

        Livewire::test(ELibrary::class)
            ->set('nisn', '111')
            ->call('cariSiswa')
            ->call('kembalikan', $lain->id);

        $this->assertNull($lain->refresh()->returned_at);
        $this->assertTrue($dilayani->exists);
    }
}
