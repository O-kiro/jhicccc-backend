<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\ReportCard;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_only_todays_schedule_for_the_students_classroom(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();

        $today = Schedule::factory()->for($classroom)->create([
            'day_of_week' => now()->dayOfWeekIso,
        ]);

        // Hari lain, dan kelas lain — keduanya tidak boleh ikut terbawa.
        Schedule::factory()->for($classroom)->create([
            'day_of_week' => now()->addDay()->dayOfWeekIso,
        ]);
        Schedule::factory()->create(['day_of_week' => now()->dayOfWeekIso]);

        $response = $this->actingAs($student, 'student')
            ->getJson(route('api.v1.overview'));

        $response->assertOk()->assertJsonCount(1, 'today_schedule');
        $this->assertSame($today->id, $response->json('today_schedule.0.id'));
    }

    public function test_it_marks_a_session_that_is_running_right_now_as_live(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();

        Schedule::factory()->for($classroom)->liveNow()->create();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.overview'))
            ->assertOk()
            ->assertJsonPath('today_schedule.0.live', true);
    }

    public function test_it_hides_unpublished_announcements(): void
    {
        $student = Student::factory()->create();

        Announcement::factory()->create(['title' => 'Terbit', 'published_at' => now()->subDay()]);
        Announcement::factory()->draft()->create(['title' => 'Draf']);
        Announcement::factory()->create(['title' => 'Terjadwal', 'published_at' => now()->addWeek()]);

        $response = $this->actingAs($student, 'student')
            ->getJson(route('api.v1.overview'));

        $response->assertOk()->assertJsonCount(1, 'announcements');
        $this->assertSame('Terbit', $response->json('announcements.0.title'));
    }

    public function test_summary_reads_from_the_students_own_report_card(): void
    {
        $student = Student::factory()->create(['streak_days' => 14]);

        ReportCard::factory()->for($student)->create([
            'average_score' => 88.30,
            'attendance_percentage' => 98.50,
        ]);

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.overview'))
            ->assertOk()
            ->assertJsonPath('summary.average_score', 88.3)
            ->assertJsonPath('summary.attendance_percentage', 98.5)
            ->assertJsonPath('summary.streak_days', 14);
    }

    public function test_summary_is_null_when_the_student_has_no_report_card_yet(): void
    {
        $student = Student::factory()->create();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.overview'))
            ->assertOk()
            ->assertJsonPath('summary.average_score', null)
            ->assertJsonPath('summary.attendance_percentage', null);
    }
}
