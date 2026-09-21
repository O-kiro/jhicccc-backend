<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentBorrowTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_catalogue_marks_books_the_student_is_holding(): void
    {
        $siswa = Student::factory()->create();
        $dipinjam = Book::factory()->create(['title' => 'A Dipinjam']);
        Book::factory()->create(['title' => 'B Tersedia']);
        BookLoan::factory()->for($siswa)->for($dipinjam)->create();

        $this->actingAs($siswa, 'student')
            ->getJson(route('api.v1.library.books'))
            ->assertOk()
            ->assertJsonPath('books.0.title', 'A Dipinjam')
            ->assertJsonPath('books.0.borrowed_by_me', true)
            ->assertJsonPath('books.1.borrowed_by_me', false)
            ->assertJsonPath('active_loans', 1);
    }

    public function test_the_catalogue_filters_by_search_and_category(): void
    {
        $siswa = Student::factory()->create();
        Book::factory()->create(['title' => 'Fisika Dasar', 'category' => 'Sains & Teknologi']);
        Book::factory()->create(['title' => 'Fiqih Ibadah', 'category' => 'Studi Islam']);

        $judul = fn (array $q) => collect($this->actingAs($siswa, 'student')
            ->getJson(route('api.v1.library.books', $q))->json('books'))->pluck('title')->all();

        $this->assertSame(['Fisika Dasar'], $judul(['q' => 'fisika']));
        $this->assertSame(['Fiqih Ibadah'], $judul(['kategori' => 'Studi Islam']));
    }

    public function test_a_student_can_borrow_a_book(): void
    {
        $siswa = Student::factory()->create();
        $buku = Book::factory()->create();

        $this->actingAs($siswa, 'student')
            ->postJson(route('api.v1.library.books.borrow', $buku), ['days' => 7])
            ->assertCreated()
            ->assertJsonPath('due_in_days', 7);

        $this->assertSame(1, $siswa->bookLoans()->active()->count());
    }

    /** Aturannya sama dengan meja petugas: satu sumber, App\Services\Sirkulasi. */
    public function test_the_same_rules_as_the_desk_apply(): void
    {
        $siswa = Student::factory()->create();
        $buku = Book::factory()->create();
        $this->actingAs($siswa, 'student')->postJson(route('api.v1.library.books.borrow', $buku))->assertCreated();

        // Pinjaman ganda.
        $this->actingAs($siswa, 'student')
            ->postJson(route('api.v1.library.books.borrow', $buku))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('buku');

        // Kuota.
        BookLoan::factory()->count(BookLoan::KUOTA - 1)->for($siswa)->create();
        $this->actingAs($siswa, 'student')
            ->postJson(route('api.v1.library.books.borrow', Book::factory()->create()))
            ->assertUnprocessable();
    }

    public function test_an_unknown_duration_is_rejected(): void
    {
        $this->actingAs(Student::factory()->create(), 'student')
            ->postJson(route('api.v1.library.books.borrow', Book::factory()->create()), ['days' => 365])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('days');
    }

    public function test_a_student_can_return_their_own_loan(): void
    {
        $siswa = Student::factory()->create();
        $pinjaman = BookLoan::factory()->for($siswa)->create();

        $this->actingAs($siswa, 'student')
            ->postJson(route('api.v1.library.loans.return', $pinjaman))
            ->assertOk();

        $this->assertNotNull($pinjaman->refresh()->returned_at);
    }

    /** 404, bukan 403 — keberadaan pinjaman siswa lain tidak perlu dibocorkan. */
    public function test_a_student_cannot_return_someone_elses_loan(): void
    {
        $pinjaman = BookLoan::factory()->create();

        $this->actingAs(Student::factory()->create(), 'student')
            ->postJson(route('api.v1.library.loans.return', $pinjaman))
            ->assertNotFound();

        $this->assertNull($pinjaman->refresh()->returned_at);
    }

    public function test_returning_twice_is_rejected(): void
    {
        $siswa = Student::factory()->create();
        $pinjaman = BookLoan::factory()->for($siswa)->returned()->create();

        $this->actingAs($siswa, 'student')
            ->postJson(route('api.v1.library.loans.return', $pinjaman))
            ->assertUnprocessable();
    }
}
