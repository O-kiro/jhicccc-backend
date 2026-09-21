<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_loans_that_are_still_out(): void
    {
        $student = Student::factory()->create();

        $open = BookLoan::factory()->for($student)->create();
        BookLoan::factory()->for($student)->returned()->create();

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.library'));

        $response->assertOk()->assertJsonCount(1, 'loans');
        $this->assertSame($open->id, $response->json('loans.0.id'));
    }

    public function test_loans_of_other_students_are_not_visible(): void
    {
        $student = Student::factory()->create();
        BookLoan::factory()->create(); // milik siswa lain

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.library'))
            ->assertOk()
            ->assertJsonCount(0, 'loans');
    }

    public function test_continue_reading_picks_the_loan_that_is_furthest_along(): void
    {
        $student = Student::factory()->create();

        BookLoan::factory()->for($student)->create(['current_page' => 20]);
        $furthest = BookLoan::factory()->for($student)
            ->for(Book::factory()->create(['total_pages' => 200]))
            ->create(['current_page' => 150]);

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.library'));

        $response->assertOk()
            ->assertJsonPath('continue_reading.id', $furthest->id)
            ->assertJsonPath('continue_reading.progress', 75)
            ->assertJsonPath('continue_reading.badge', 'Sedang dibaca');
    }

    /** Buku yang dipinjam tapi belum dibuka tidak layak disebut "sedang dibaca". */
    public function test_continue_reading_is_null_when_nothing_has_been_opened(): void
    {
        $student = Student::factory()->create();
        BookLoan::factory()->for($student)->create(['current_page' => 0]);

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.library'))
            ->assertOk()
            ->assertJsonPath('continue_reading', null);
    }

    public function test_it_requires_authentication(): void
    {
        $this->getJson(route('api.v1.library'))->assertUnauthorized();
    }
}
