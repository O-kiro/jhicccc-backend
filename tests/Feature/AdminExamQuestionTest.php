<?php

namespace Tests\Feature;

use App\Filament\Resources\Exams\Pages\EditExam;
use App\Filament\Resources\Exams\RelationManagers\QuestionsRelationManager;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class AdminExamQuestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    /**
     * @return array{Exam, ExamQuestion}
     */
    private function soal(): array
    {
        $exam = Exam::factory()->create();
        $question = ExamQuestion::factory()->for($exam)->create(['number' => 1]);

        ExamQuestionOption::factory()->for($question, 'question')->create(['key' => 'A']);
        ExamQuestionOption::factory()->for($question, 'question')->correct()->create(['key' => 'B']);

        return [$exam, $question];
    }

    private function relationManager(Exam $exam): Testable
    {
        return Livewire::test(QuestionsRelationManager::class, [
            'ownerRecord' => $exam,
            'pageClass' => EditExam::class,
        ]);
    }

    /**
     * Jebakan yang mahal: ExamQuestionOption menandai is_correct sebagai
     * #[Hidden], sedangkan Repeater Filament mengisi formulir lewat
     * attributesToArray() yang menghormati penanda itu. Tanpa pemulihan
     * khusus, membuka soal lalu menyimpannya akan menghapus kunci jawaban
     * tanpa pesan apa pun.
     */
    public function test_opening_a_question_shows_the_existing_answer_key(): void
    {
        [$exam, $question] = $this->soal();

        $component = $this->relationManager($exam)->mountTableAction('edit', $question);

        // Repeater memberi tiap barisnya kunci acak, jadi isinya dipetakan
        // ulang berdasarkan huruf opsi.
        $opsi = collect($component->get('mountedActions')[0]['data']['options'] ?? [])
            ->mapWithKeys(fn (array $row): array => [$row['key'] => (bool) $row['is_correct']]);

        $this->assertSame(['A' => false, 'B' => true], $opsi->all());
    }

    public function test_saving_a_question_without_touching_the_options_keeps_the_answer_key(): void
    {
        [$exam, $question] = $this->soal();

        $this->relationManager($exam)
            ->callTableAction('edit', $question)
            ->assertHasNoActionErrors();

        $this->assertSame(
            'B',
            $question->refresh()->options->firstWhere('is_correct', true)?->key,
            'Kunci jawaban hilang setelah soal disimpan ulang.',
        );
    }

    /**
     * Alur yang menjadi alasan seluruh panel ini dibuat: guru mengarang soal,
     * siswa mengerjakannya, dan kunci jawabannya tidak pernah ikut terkirim.
     */
    public function test_a_question_created_by_a_teacher_reaches_the_student_without_the_answer_key(): void
    {
        $classroom = Classroom::factory()->create();
        $exam = Exam::factory()->for($classroom)->live()->create();
        $student = Student::factory()->for($classroom)->create();

        $this->relationManager($exam)->callTableAction('create', data: [
            'number' => 1,
            'type' => 'Pilihan Ganda',
            'body' => 'Siapa nabi yang diperintahkan menyembelih putranya?',
            'options' => [
                ['key' => 'A', 'body' => 'Nabi Musa AS', 'is_correct' => false],
                ['key' => 'B', 'body' => 'Nabi Ibrahim AS', 'is_correct' => true],
            ],
        ])->assertHasNoActionErrors();

        $response = $this->actingAs($student, 'student')
            ->getJson(route('api.v1.exam-session'));

        $response->assertOk()
            ->assertJsonPath('questions.0.body', 'Siapa nabi yang diperintahkan menyembelih putranya?')
            ->assertJsonCount(2, 'questions.0.options');

        $this->assertStringNotContainsString('is_correct', $response->getContent());
        $this->assertStringNotContainsString('Nabi Ibrahim AS', $response->json('questions.0.options.1.key'));
    }

    /** Dua kunci jawaban pada satu soal ditolak sebelum tersimpan. */
    public function test_a_question_with_two_answer_keys_is_rejected(): void
    {
        $exam = Exam::factory()->create();

        $this->relationManager($exam)->callTableAction('create', data: [
            'number' => 1,
            'type' => 'Pilihan Ganda',
            'body' => 'Soal dengan dua kunci jawaban.',
            'options' => [
                ['key' => 'A', 'body' => 'Pilihan A', 'is_correct' => true],
                ['key' => 'B', 'body' => 'Pilihan B', 'is_correct' => true],
            ],
        ])->assertHasActionErrors();

        $this->assertSame(0, ExamQuestion::query()->count());
    }
}
