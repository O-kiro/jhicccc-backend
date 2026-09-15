<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentReportCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_students_report_card_summary(): void
    {
        $student = Student::factory()->create();

        ReportCard::factory()->for($student)->create([
            'average_score' => 88.30,
            'class_rank' => 2,
            'class_size' => 32,
            'attendance_percentage' => 98.50,
        ]);

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.report-card'))
            ->assertOk()
            ->assertJsonPath('report_card.average_score', 88.3)
            ->assertJsonPath('report_card.class_rank', 2)
            ->assertJsonPath('report_card.class_size', 32);
    }

    public function test_grade_history_averages_assessments_per_month(): void
    {
        $student = Student::factory()->create();
        $subject = Subject::factory()->create();
        ReportCard::factory()->for($student)->create();

        // Dua nilai di bulan yang sama harus menjadi satu titik rata-rata.
        Assessment::factory()->for($student)->for($subject)->create([
            'score' => 80, 'assessed_on' => '2025-08-10',
        ]);
        Assessment::factory()->for($student)->for($subject)->create([
            'score' => 90, 'assessed_on' => '2025-08-24',
        ]);
        Assessment::factory()->for($student)->for($subject)->create([
            'score' => 70, 'assessed_on' => '2025-09-05',
        ]);

        $response = $this->actingAs($student, 'student')
            ->getJson(route('api.v1.report-card'));

        $response->assertOk()->assertJsonCount(2, 'grade_history');
        $this->assertEquals(85, $response->json('grade_history.0.score'));
        $this->assertEquals(70, $response->json('grade_history.1.score'));
    }

    public function test_it_returns_404_when_no_report_card_exists(): void
    {
        $student = Student::factory()->create();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.report-card'))
            ->assertNotFound();
    }

    public function test_a_student_never_sees_another_students_data(): void
    {
        $student = Student::factory()->create();
        $other = Student::factory()->create();
        $teacher = Teacher::factory()->create();

        ReportCard::factory()->for($student)->create(['average_score' => 70.00]);
        ReportCard::factory()->for($other)->create(['average_score' => 99.00]);

        Assessment::factory()->for($other)->create();
        TeacherFeedback::factory()->for($other)->for($teacher)->create();

        $response = $this->actingAs($student, 'student')
            ->getJson(route('api.v1.report-card'));

        $response->assertOk()
            ->assertJsonPath('report_card.average_score', 70)
            ->assertJsonCount(0, 'recent_assessments')
            ->assertJsonCount(0, 'teacher_feedback');
    }
}
