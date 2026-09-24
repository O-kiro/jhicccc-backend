<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\DisciplineRecord;
use App\Models\DisciplineRule;
use App\Models\LessonPlan;
use App\Models\ReportCard;
use App\Models\ReportUpload;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherActivity;
use App\Models\TeacherFeedback;
use App\Models\TeachingMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Empat menu Portal Guru yang menyusul dari dokumen Figma: Modul
 * Pembelajaran, Bahan Ajar & LKPD, Jurnal Harian, RDM, dan Lapor Tatib.
 */
class GuruPerangkatTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $guru;

    private Classroom $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = Teacher::factory()->create();
        $this->kelas = Classroom::factory()->create();
        // Kelas dianggap diampu karena ada jadwalnya.
        Schedule::factory()->for($this->guru)->for($this->kelas)->create();
    }

    // ---------- Modul Pembelajaran ----------

    public function test_lesson_plans_are_split_into_mine_colleagues_and_archive(): void
    {
        LessonPlan::factory()->for($this->guru)->create(['title' => 'Punyaku']);
        LessonPlan::factory()->for($this->guru)->arsip()->create(['title' => 'Arsipku']);
        LessonPlan::factory()->create(['title' => 'Punya rekan']);

        $res = fn (string $tab) => $this->actingAs($this->guru, 'teacher')
            ->getJson(route('api.v1.guru.modul-ajar.index', ['tab' => $tab]));

        $res('saya')->assertOk()
            ->assertJsonCount(1, 'plans')
            ->assertJsonPath('plans.0.title', 'Punyaku')
            ->assertJsonPath('plans.0.is_mine', true)
            ->assertJsonPath('counts.saya', 1)
            ->assertJsonPath('counts.rekan', 1)
            ->assertJsonPath('counts.arsip', 1);

        $res('rekan')->assertJsonCount(1, 'plans')->assertJsonPath('plans.0.is_mine', false);
        $res('arsip')->assertJsonCount(1, 'plans')->assertJsonPath('plans.0.title', 'Arsipku');
    }

    public function test_a_teacher_manages_only_their_own_lesson_plan(): void
    {
        $mapel = Subject::factory()->create();

        $id = $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.modul-ajar.store'), [
                'title' => 'Klasifikasi Makhluk Hidup',
                'subject_id' => $mapel->id,
                'classroom_id' => $this->kelas->id,
                'time_range' => '07.45–09.15',
                'url' => 'https://drive.google.com/modul',
            ])->assertCreated()->json('id');

        $this->actingAs($this->guru, 'teacher')
            ->putJson(route('api.v1.guru.modul-ajar.update', $id), [
                'title' => 'Klasifikasi Makhluk Hidup (revisi)',
                'status' => 'arsip',
            ])->assertOk();

        $this->assertSame('arsip', LessonPlan::query()->find($id)->status);

        $punyaOrangLain = LessonPlan::factory()->create();

        $this->actingAs($this->guru, 'teacher')
            ->putJson(route('api.v1.guru.modul-ajar.update', $punyaOrangLain), ['title' => 'Susupan'])
            ->assertNotFound();
        $this->actingAs($this->guru, 'teacher')
            ->deleteJson(route('api.v1.guru.modul-ajar.destroy', $punyaOrangLain))
            ->assertNotFound();

        $this->actingAs($this->guru, 'teacher')
            ->deleteJson(route('api.v1.guru.modul-ajar.destroy', $id))
            ->assertOk();
        $this->assertDatabaseMissing('lesson_plans', ['id' => $id]);
    }

    public function test_lesson_plan_links_must_be_http(): void
    {
        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.modul-ajar.store'), [
                'title' => 'Jebakan',
                'url' => 'javascript:alert(1)',
            ])->assertStatus(422)->assertJsonValidationErrors('url');
    }

    // ---------- Bahan Ajar & LKPD ----------

    public function test_teaching_materials_are_private_to_their_owner(): void
    {
        TeachingMaterial::factory()->for($this->guru)->create(['title' => 'LKPD Fotosintesis']);
        TeachingMaterial::factory()->for($this->guru)->create(['type' => 'bahan_ajar', 'title' => 'Rangkuman Bab 1']);
        TeachingMaterial::factory()->create(['title' => 'Milik guru lain']);

        $this->actingAs($this->guru, 'teacher')
            ->getJson(route('api.v1.guru.bahan-ajar.index'))
            ->assertOk()
            ->assertJsonCount(2, 'materials')
            ->assertJsonPath('counts.lkpd', 1)
            ->assertJsonPath('counts.bahan_ajar', 1);

        $this->actingAs($this->guru, 'teacher')
            ->getJson(route('api.v1.guru.bahan-ajar.index', ['jenis' => 'lkpd']))
            ->assertJsonCount(1, 'materials')
            ->assertJsonPath('materials.0.type_label', 'LKPD');
    }

    public function test_a_teacher_cannot_edit_another_teachers_material(): void
    {
        $lain = TeachingMaterial::factory()->create();

        $this->actingAs($this->guru, 'teacher')
            ->putJson(route('api.v1.guru.bahan-ajar.update', $lain), ['type' => 'lkpd', 'title' => 'Susupan'])
            ->assertNotFound();
    }

    // ---------- Jurnal Harian ----------

    public function test_a_daily_activity_is_recorded_with_an_optional_photo(): void
    {
        Storage::fake('public');

        $id = $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.jurnal-harian.store'), [
                'date' => today()->toDateString(),
                'classroom_id' => $this->kelas->id,
                'activity' => 'Mendampingi kerja kelompok di laboratorium.',
                'photo' => UploadedFile::fake()->image('bukti.jpg'),
            ])->assertCreated()->json('id');

        $kegiatan = TeacherActivity::query()->find($id);
        $this->assertNotNull($kegiatan->photo_path);
        Storage::disk('public')->assertExists($kegiatan->photo_path);

        $this->actingAs($this->guru, 'teacher')
            ->getJson(route('api.v1.guru.jurnal-harian.index'))
            ->assertOk()
            ->assertJsonCount(1, 'activities')
            ->assertJsonPath('counts.tahun_ini', 1);

        // Menghapus kegiatan ikut membuang fotonya.
        $this->actingAs($this->guru, 'teacher')
            ->deleteJson(route('api.v1.guru.jurnal-harian.destroy', $id))
            ->assertOk();
        Storage::disk('public')->assertMissing($kegiatan->photo_path);
    }

    public function test_a_daily_activity_cannot_be_dated_in_the_future(): void
    {
        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.jurnal-harian.store'), [
                'date' => today()->addWeek()->toDateString(),
                'activity' => 'Belum terjadi',
            ])->assertStatus(422)
            ->assertJsonPath('errors.date.0', 'Kegiatan tidak bisa dicatat untuk tanggal yang belum tiba.');
    }

    public function test_daily_activities_of_other_teachers_are_hidden(): void
    {
        $lain = TeacherActivity::factory()->create();

        $this->actingAs($this->guru, 'teacher')
            ->getJson(route('api.v1.guru.jurnal-harian.index'))
            ->assertJsonCount(0, 'activities');

        $this->actingAs($this->guru, 'teacher')
            ->deleteJson(route('api.v1.guru.jurnal-harian.destroy', $lain))
            ->assertNotFound();
    }

    // ---------- RDM ----------

    public function test_a_report_file_is_uploaded_for_a_class_the_teacher_teaches(): void
    {
        Storage::fake('public');

        $id = $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.rdm.store'), [
                'classroom_id' => $this->kelas->id,
                'academic_year' => '2026/2027',
                'semester' => 'Ganjil',
                'file' => UploadedFile::fake()->create('rapor.pdf', 200, 'application/pdf'),
            ])->assertCreated()->json('id');

        $berkas = ReportUpload::query()->find($id);
        Storage::disk('public')->assertExists($berkas->file_path);
        $this->assertSame('rapor.pdf', $berkas->original_name);

        $this->actingAs($this->guru, 'teacher')
            ->deleteJson(route('api.v1.guru.rdm.destroy', $id))
            ->assertOk();
        Storage::disk('public')->assertMissing($berkas->file_path);
    }

    public function test_report_uploads_reject_other_classes_and_wrong_file_types(): void
    {
        Storage::fake('public');
        $kelasLain = Classroom::factory()->create();

        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.rdm.store'), [
                'classroom_id' => $kelasLain->id,
                'academic_year' => '2026/2027',
                'semester' => 'Ganjil',
                'file' => UploadedFile::fake()->create('rapor.pdf', 10, 'application/pdf'),
            ])->assertStatus(422)
            ->assertJsonPath('errors.classroom_id.0', 'Kelas ini bukan kelas yang Anda ampu.');

        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.rdm.store'), [
                'classroom_id' => $this->kelas->id,
                'academic_year' => '2026/2027',
                'semester' => 'Ganjil',
                'file' => UploadedFile::fake()->create('virus.exe', 10),
            ])->assertStatus(422)->assertJsonValidationErrors('file');
    }

    public function test_teacher_notes_reach_the_student_report_card(): void
    {
        $siswa = Student::factory()->for($this->kelas)->create();

        $id = $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.rdm.catatan.store'), [
                'student_id' => $siswa->id,
                'role' => 'Guru Mapel',
                'body' => 'Aktif bertanya dan konsisten mengerjakan tugas.',
            ])->assertCreated()->json('id');

        // Muncul di Rapor Digital siswa.
        ReportCard::factory()->for($siswa)->create();
        $this->actingAs($siswa, 'student')
            ->getJson(route('api.v1.report-card'))
            ->assertJsonPath('teacher_feedback.0.body', 'Aktif bertanya dan konsisten mengerjakan tugas.');

        $this->app['auth']->forgetGuards();

        $punyaOrangLain = TeacherFeedback::factory()->create();
        $this->actingAs($this->guru, 'teacher')
            ->deleteJson(route('api.v1.guru.rdm.catatan.destroy', $punyaOrangLain))
            ->assertNotFound();

        $this->actingAs($this->guru, 'teacher')
            ->deleteJson(route('api.v1.guru.rdm.catatan.destroy', $id))
            ->assertOk();
    }

    public function test_notes_cannot_be_written_for_students_outside_the_teachers_classes(): void
    {
        $orangLain = Student::factory()->create();

        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.rdm.catatan.store'), [
                'student_id' => $orangLain->id,
                'role' => 'Guru Mapel',
                'body' => 'Bukan siswa saya',
            ])->assertStatus(422)
            ->assertJsonPath('errors.student_id.0', 'Siswa ini bukan siswa di kelas yang Anda ampu.');
    }

    // ---------- Lapor Tatib ----------

    public function test_a_discipline_report_copies_the_rule_points(): void
    {
        $siswa = Student::factory()->for($this->kelas)->create();
        $aturan = DisciplineRule::factory()->create(['points' => 25, 'is_active' => true]);

        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.tatib.store'), [
                'student_id' => $siswa->id,
                'discipline_rule_id' => $aturan->id,
                'occurred_on' => today()->toDateString(),
                'note' => 'Terlambat 25 menit.',
            ])->assertCreated()
            ->assertJsonPath('points', 25);

        // Bobot aturan berubah kelak tidak boleh menulis ulang riwayat.
        $aturan->update(['points' => 5]);
        $this->assertSame(25, DisciplineRecord::query()->sole()->points);

        $this->actingAs($this->guru, 'teacher')
            ->getJson(route('api.v1.guru.tatib.index'))
            ->assertOk()
            ->assertJsonCount(1, 'reports')
            ->assertJsonPath('reports.0.points', 25)
            ->assertJsonPath('reports.0.student', $siswa->name);
    }

    public function test_a_discipline_report_rejects_other_students_and_old_dates(): void
    {
        $orangLain = Student::factory()->create();
        $siswa = Student::factory()->for($this->kelas)->create();
        $aturan = DisciplineRule::factory()->create(['is_active' => true]);

        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.tatib.store'), [
                'student_id' => $orangLain->id,
                'discipline_rule_id' => $aturan->id,
                'occurred_on' => today()->toDateString(),
            ])->assertStatus(422)->assertJsonValidationErrors('student_id');

        $this->actingAs($this->guru, 'teacher')
            ->postJson(route('api.v1.guru.tatib.store'), [
                'student_id' => $siswa->id,
                'discipline_rule_id' => $aturan->id,
                'occurred_on' => today()->subDays(90)->toDateString(),
            ])->assertStatus(422)
            ->assertJsonPath('errors.occurred_on.0', 'Paling jauh 60 hari ke belakang.');
    }

    public function test_only_teachers_reach_these_endpoints(): void
    {
        $siswa = Student::factory()->create();

        $this->actingAs($siswa, 'student')
            ->getJson(route('api.v1.guru.modul-ajar.index'))
            ->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->actingAs($siswa, 'student')
            ->getJson(route('api.v1.guru.rdm.index'))
            ->assertUnauthorized();
    }
}
