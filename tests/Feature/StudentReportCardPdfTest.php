<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\TeacherFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentReportCardPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_pdf_attachment(): void
    {
        $student = Student::factory()->create(['name' => 'Akhnaf Meyfan']);
        ReportCard::factory()->for($student)->create([
            'semester' => 'Ganjil',
            'academic_year' => '2025/2026',
        ]);
        Assessment::factory()->for($student)->create();
        TeacherFeedback::factory()->for($student)->create();

        $response = $this->actingAs($student, 'student')
            ->get(route('api.v1.report-card.pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            'attachment;',
            (string) $response->headers->get('content-disposition'),
        );
        // %PDF- adalah penanda berkas PDF yang sah.
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_it_returns_404_when_no_report_card_exists(): void
    {
        $student = Student::factory()->create();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.report-card.pdf'))
            ->assertNotFound();
    }

    public function test_it_requires_authentication(): void
    {
        $this->getJson(route('api.v1.report-card.pdf'))->assertUnauthorized();
    }
}
