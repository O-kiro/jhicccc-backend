<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\ExamResult;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentExamSessionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat satu ujian berjalan berisi satu soal dengan dua opsi, di mana
     * opsi B adalah kunci jawabannya.
     *
     * @return array{Student, ExamQuestion}
     */
    private function liveExam(): array
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();
        $exam = Exam::factory()->for($classroom)->live()->create();

        $question = ExamQuestion::factory()->for($exam)->create(['number' => 1]);
        // Relasinya bernama "question", bukan "examQuestion", jadi harus
        // disebut eksplisit.
        ExamQuestionOption::factory()->for($question, 'question')->create(['key' => 'A']);
        ExamQuestionOption::factory()->for($question, 'question')->correct()->create(['key' => 'B']);

        return [$student, $question];
    }

    public function test_it_returns_the_exam_that_is_running_right_now(): void
    {
        [$student, $question] = $this->liveExam();

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.exam-session'));

        $response->assertOk()
            ->assertJsonPath('exam.total_questions', 1)
            ->assertJsonPath('questions.0.id', $question->id)
            ->assertJsonCount(2, 'questions.0.options');

        $this->assertGreaterThan(0, $response->json('exam.remaining_seconds'));
    }

    /**
     * Kunci jawaban tidak boleh sampai ke portal siswa — siapa pun bisa
     * membaca respons jaringan dari peramban.
     */
    public function test_it_never_exposes_the_answer_key(): void
    {
        [$student] = $this->liveExam();

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.exam-session'));

        $response->assertOk();
        $this->assertStringNotContainsString('is_correct', $response->getContent());
    }

    public function test_it_returns_404_when_no_exam_is_running(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();

        Exam::factory()->for($classroom)->create(); // besok, belum mulai

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.exam-session'))
            ->assertNotFound();
    }

    public function test_saved_answers_come_back_so_a_session_can_be_resumed(): void
    {
        [$student, $question] = $this->liveExam();

        ExamAnswer::query()->create([
            'exam_question_id' => $question->id,
            'student_id' => $student->id,
            'choice' => 'A',
            'is_flagged' => true,
        ]);

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.exam-session'))
            ->assertOk()
            ->assertJsonPath('answers.1.choice', 'A')
            ->assertJsonPath('answers.1.flagged', true);
    }

    public function test_it_stores_an_answer(): void
    {
        [$student, $question] = $this->liveExam();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.answers.store'), [
                'question_id' => $question->id,
                'choice' => 'B',
                'flagged' => false,
            ])
            ->assertOk();

        $this->assertDatabaseHas('exam_answers', [
            'exam_question_id' => $question->id,
            'student_id' => $student->id,
            'choice' => 'B',
        ]);
    }

    /** Dipanggil tiap kali siswa mengganti pilihan, jadi tidak boleh menumpuk. */
    public function test_saving_twice_overwrites_instead_of_duplicating(): void
    {
        [$student, $question] = $this->liveExam();

        foreach (['A', 'B'] as $choice) {
            $this->actingAs($student, 'student')
                ->postJson(route('api.v1.exam-session.answers.store'), [
                    'question_id' => $question->id,
                    'choice' => $choice,
                    'flagged' => false,
                ])
                ->assertOk();
        }

        $this->assertSame(1, ExamAnswer::query()->count());
        $this->assertSame('B', ExamAnswer::query()->sole()->choice);
    }

    public function test_it_rejects_an_option_that_does_not_belong_to_the_question(): void
    {
        [$student, $question] = $this->liveExam();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.answers.store'), [
                'question_id' => $question->id,
                'choice' => 'E',
                'flagged' => false,
            ])
            ->assertUnprocessable();

        $this->assertSame(0, ExamAnswer::query()->count());
    }

    public function test_it_rejects_a_question_from_another_classroom(): void
    {
        [$student] = $this->liveExam();

        $other = Exam::factory()->live()->create();
        $foreign = ExamQuestion::factory()->for($other)->create(['number' => 1]);

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.answers.store'), [
                'question_id' => $foreign->id,
                'choice' => null,
                'flagged' => true,
            ])
            ->assertNotFound();

        $this->assertSame(0, ExamAnswer::query()->count());
    }

    /** Ujian yang sudah lewat tidak boleh lagi menerima jawaban. */
    public function test_it_rejects_an_answer_after_the_exam_has_ended(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();
        $exam = Exam::factory()->for($classroom)->finished()->create();
        $question = ExamQuestion::factory()->for($exam)->create(['number' => 1]);

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.answers.store'), [
                'question_id' => $question->id,
                'choice' => null,
                'flagged' => true,
            ])
            ->assertNotFound();
    }

    /**
     * Penilaian harus dikerjakan server: kunci jawaban tidak pernah dikirim ke
     * portal, jadi klien memang tidak bisa menghitungnya sendiri.
     */
    public function test_finishing_scores_the_answers_server_side(): void
    {
        [$student, $question] = $this->liveExam();

        // Soal kedua sengaja dibiarkan tidak dijawab → nilai harus 50.
        $kedua = ExamQuestion::factory()
            ->for($question->exam)
            ->create(['number' => 2]);
        ExamQuestionOption::factory()->for($kedua, 'question')->correct()->create(['key' => 'A']);

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.answers.store'), [
                'question_id' => $question->id,
                'choice' => 'B', // kunci jawaban soal pertama
                'flagged' => false,
            ])
            ->assertOk();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.finish'))
            ->assertOk()
            ->assertJsonPath('score', 50)
            ->assertJsonPath('correct', 1)
            ->assertJsonPath('total_questions', 2);

        $this->assertDatabaseHas('exam_results', [
            'exam_id' => $question->exam_id,
            'student_id' => $student->id,
            'score' => 50,
        ]);
    }

    public function test_a_wrong_answer_scores_zero(): void
    {
        [$student, $question] = $this->liveExam();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.answers.store'), [
                'question_id' => $question->id,
                'choice' => 'A', // opsi salah
                'flagged' => false,
            ])
            ->assertOk();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.finish'))
            ->assertOk()
            ->assertJsonPath('score', 0);
    }

    /** Sesi yang sudah dikirim tidak boleh dibuka lagi walau waktunya tersisa. */
    public function test_the_session_closes_once_it_has_been_submitted(): void
    {
        [$student] = $this->liveExam();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.finish'))
            ->assertOk();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.exam-session'))
            ->assertNotFound();
    }

    public function test_answers_are_rejected_once_the_session_has_been_submitted(): void
    {
        [$student, $question] = $this->liveExam();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.finish'))
            ->assertOk();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.answers.store'), [
                'question_id' => $question->id,
                'choice' => 'B',
                'flagged' => false,
            ])
            ->assertNotFound();
    }

    /** Mengirim dua kali tidak boleh menghasilkan dua baris nilai. */
    public function test_submitting_twice_does_not_duplicate_the_result(): void
    {
        [$student] = $this->liveExam();

        foreach (range(1, 2) as $ignored) {
            $this->actingAs($student, 'student')
                ->postJson(route('api.v1.exam-session.finish'))
                ->assertOk();
        }

        $this->assertSame(1, ExamResult::query()->count());
    }

    public function test_the_finished_exam_shows_up_in_the_results_list(): void
    {
        [$student, $question] = $this->liveExam();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.answers.store'), [
                'question_id' => $question->id,
                'choice' => 'B',
                'flagged' => false,
            ])
            ->assertOk();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.exam-session.finish'))
            ->assertOk();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.exams'))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.score', 100);
    }
}
