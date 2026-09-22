<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruJurnalTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $guru;

    private Schedule $sesi;

    protected function setUp(): void
    {
        parent::setUp();

        // Rabu, 23 September 2026, pukul 10.00.
        $this->travelTo(CarbonImmutable::parse('2026-09-23 10:00:00'));

        $this->guru = Teacher::factory()->create();
        $kelas = Classroom::factory()->create();
        Student::factory()->count(3)->for($kelas)->create();

        $this->sesi = Schedule::factory()->for($this->guru)->for($kelas)->create([
            'day_of_week' => 3,
            'starts_at' => '08:00:00',
            'ends_at' => '09:30:00',
        ]);
    }

    public function test_the_overview_lists_only_this_teachers_sessions_today(): void
    {
        Schedule::factory()->create(['day_of_week' => 3]); // guru lain
        Schedule::factory()->for($this->guru)->create(['day_of_week' => 4]); // besok

        $this->actingAs($this->guru, 'teacher')
            ->getJson(route('api.v1.guru.overview'))
            ->assertOk()
            ->assertJsonCount(1, 'today_schedule')
            ->assertJsonPath('today_schedule.0.id', $this->sesi->id)
            ->assertJsonPath('today_schedule.0.journal_filled', false)
            ->assertJsonPath('summary.sessions_today', 1)
            ->assertJsonPath('summary.classes', 2)
            ->assertJsonPath('summary.students', 3)
            // Sesi hari ini ditambah sesi Kamis — yang Kamis lalu (17/9)
            // masih dalam rentang tagihan tujuh hari.
            ->assertJsonPath('summary.journals_pending', 2);
    }

    public function test_the_week_is_grouped_by_day_with_saturday_always_present(): void
    {
        Schedule::factory()->for($this->guru)->create(['day_of_week' => 1, 'starts_at' => '07:00:00', 'ends_at' => '08:00:00']);

        $res = $this->actingAs($this->guru, 'teacher')->getJson(route('api.v1.guru.jadwal'))->assertOk();

        $this->assertSame(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'], $res->json('days.*.label'));
        $res->assertJsonPath('days.2.is_today', true)
            ->assertJsonCount(1, 'days.0.sessions')
            ->assertJsonPath('summary.sessions', 2)
            ->assertJsonPath('summary.minutes', 150);
    }

    public function test_a_session_that_has_not_started_is_not_pending_yet(): void
    {
        Schedule::factory()->for($this->guru)->create(['day_of_week' => 3, 'starts_at' => '13:00:00', 'ends_at' => '14:00:00']);

        $this->actingAs($this->guru, 'teacher')
            ->getJson(route('api.v1.guru.jurnal.index'))
            ->assertOk()
            ->assertJsonCount(1, 'pending')
            ->assertJsonPath('pending.0.schedule_id', $this->sesi->id)
            ->assertJsonPath('pending.0.date', '2026-09-23')
            ->assertJsonPath('pending.0.class_size', 3);
    }

    public function test_filling_a_journal_twice_updates_it_instead_of_duplicating(): void
    {
        $isi = ['schedule_id' => $this->sesi->id, 'date' => '2026-09-23', 'topic' => 'Limit fungsi', 'present_count' => 3];

        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.jurnal.store'), $isi)
            ->assertCreated();

        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.jurnal.store'), [...$isi, 'topic' => 'Limit fungsi (lanjutan)'])
            ->assertOk()
            ->assertJsonPath('updated', true);

        $this->assertSame(1, TeachingJournal::query()->count());
        $this->assertSame('Limit fungsi (lanjutan)', TeachingJournal::query()->first()->topic);
        $this->assertSame($this->guru->id, TeachingJournal::query()->first()->teacher_id);

        $this->actingAs($this->guru, 'teacher')
            ->getJson(route('api.v1.guru.jurnal.index'))
            ->assertJsonCount(0, 'pending')
            ->assertJsonPath('journals.0.topic', 'Limit fungsi (lanjutan)');
    }

    /** Jurnal yang dicatat admin (teacher_id kosong) juga ditemukan. */
    public function test_a_journal_recorded_from_the_admin_panel_is_updated_not_duplicated(): void
    {
        TeachingJournal::query()->create([
            'schedule_id' => $this->sesi->id,
            'date' => '2026-09-23',
            'topic' => 'Dari panel',
        ]);

        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.jurnal.store'), [
                'schedule_id' => $this->sesi->id, 'date' => '2026-09-23', 'topic' => 'Dari portal',
            ])->assertOk();

        $this->assertSame(['Dari portal'], TeachingJournal::query()->pluck('topic')->all());
    }

    public function test_the_date_must_fall_on_the_sessions_weekday(): void
    {
        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.jurnal.store'), [
                'schedule_id' => $this->sesi->id, 'date' => '2026-09-22', 'topic' => 'Salah hari',
            ])->assertStatus(422)
            ->assertJsonPath('errors.date.0', 'Jadwal ini hari Rabu, sedangkan tanggal itu hari Selasa.');
    }

    public function test_future_dates_and_other_teachers_sessions_are_rejected(): void
    {
        $lain = Schedule::factory()->create(['day_of_week' => 3]);

        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.jurnal.store'), [
                'schedule_id' => $this->sesi->id, 'date' => '2026-09-30', 'topic' => 'Belum terjadi',
            ])->assertStatus(422)
            ->assertJsonPath('errors.date.0', 'Jurnal tidak bisa diisi untuk tanggal yang belum tiba.');

        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.jurnal.store'), [
                'schedule_id' => $lain->id, 'date' => '2026-09-23', 'topic' => 'Bukan kelasku',
            ])->assertStatus(422)->assertJsonValidationErrors('schedule_id');
    }

    public function test_attendance_cannot_exceed_the_class_size(): void
    {
        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.jurnal.store'), [
                'schedule_id' => $this->sesi->id, 'date' => '2026-09-23', 'topic' => 'Uji', 'present_count' => 4,
            ])->assertStatus(422)
            ->assertJsonPath('errors.present_count.0', 'Jumlah hadir melebihi jumlah siswa kelas ini (3).');
    }

    public function test_a_teacher_can_only_delete_journals_of_their_own_sessions(): void
    {
        $milikku = TeachingJournal::factory()->create(['schedule_id' => $this->sesi->id]);
        $milikLain = TeachingJournal::factory()->create();

        $this->actingAs($this->guru, 'teacher')
            ->deleteJson(route('api.v1.guru.jurnal.destroy', $milikLain))
            ->assertNotFound();

        $this->actingAs($this->guru, 'teacher')
            ->deleteJson(route('api.v1.guru.jurnal.destroy', $milikku))
            ->assertOk();

        $this->assertModelMissing($milikku);
        $this->assertModelExists($milikLain);
    }
}
