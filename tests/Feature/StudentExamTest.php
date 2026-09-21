<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_upcoming_excludes_exams_that_already_ended(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();

        $next = Exam::factory()->for($classroom)->create();
        Exam::factory()->for($classroom)->finished()->create();

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.exams'));

        $response->assertOk()->assertJsonCount(1, 'upcoming');
        $this->assertSame($next->id, $response->json('upcoming.0.id'));
    }

    public function test_upcoming_excludes_other_classrooms(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();

        Exam::factory()->create(); // kelas lain

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.exams'))
            ->assertOk()
            ->assertJsonCount(0, 'upcoming');
    }

    /**
     * is_live menentukan kapan tombol "Masuk Ke Dalam Ujian" muncul di portal,
     * jadi harus benar-benar sepadan dengan apa yang dilayani /exam-session.
     */
    public function test_an_exam_in_progress_is_live_and_has_no_countdown(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();

        Exam::factory()->for($classroom)->live()->create();

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.exams'));

        $response->assertOk()
            ->assertJsonPath('upcoming.0.is_live', true)
            ->assertJsonPath('upcoming.0.starts_in_seconds', null);
    }

    public function test_an_exam_that_has_not_started_has_a_countdown_but_is_not_live(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();

        Exam::factory()->for($classroom)->create(['starts_at' => now()->addHour()]);

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.exams'));

        $response->assertOk()->assertJsonPath('upcoming.0.is_live', false);
        $this->assertGreaterThan(0, $response->json('upcoming.0.starts_in_seconds'));
    }

    public function test_results_are_scoped_to_the_signed_in_student(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();
        $classmate = Student::factory()->for($classroom)->create();

        $mine = ExamResult::factory()->for($student)->create(['score' => 91]);
        ExamResult::factory()->for($classmate)->create(['score' => 55]);

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.exams'));

        $response->assertOk()->assertJsonCount(1, 'results');
        $this->assertSame($mine->id, $response->json('results.0.id'));
        $this->assertSame(91, $response->json('results.0.score'));
    }
}
