<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\ModuleCompletion;
use App\Models\ReportCard;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class GuruKelasTest extends TestCase
{
    use RefreshDatabase;

    public function test_courses_show_the_average_progress_of_enrolled_students(): void
    {
        $guru = Teacher::factory()->create();
        $kursus = Course::factory()->for($guru)->create();
        $modul = collect([1, 2])->map(fn ($n) => CourseModule::factory()->for($kursus)->create(['number' => $n]));

        [$a, $b] = Student::factory()->count(2)->create();
        Enrollment::factory()->create(['student_id' => $a->id, 'course_id' => $kursus->id]);
        Enrollment::factory()->create(['student_id' => $b->id, 'course_id' => $kursus->id]);
        ModuleCompletion::query()->create(['student_id' => $a->id, 'course_module_id' => $modul[0]->id, 'completed_at' => now()]);
        ModuleCompletion::query()->create(['student_id' => $a->id, 'course_module_id' => $modul[1]->id, 'completed_at' => now()]);
        ModuleCompletion::query()->create(['student_id' => $b->id, 'course_module_id' => $modul[0]->id, 'completed_at' => now()]);

        // Penyelesaian dari siswa yang tidak terdaftar tidak ikut dihitung.
        $keluar = Student::factory()->create();
        ModuleCompletion::query()->create(['student_id' => $keluar->id, 'course_module_id' => $modul[1]->id, 'completed_at' => now()]);

        Course::factory()->create(); // milik guru lain

        $this->actingAs($guru, 'teacher')
            ->getJson(route('api.v1.guru.kelas'))
            ->assertOk()
            ->assertJsonCount(1, 'courses')
            ->assertJsonPath('courses.0.students', 2)
            ->assertJsonPath('courses.0.average_progress', 75)
            ->assertJsonPath('courses.0.modules.0.completed', 2)
            ->assertJsonPath('courses.0.modules.1.completed', 1);
    }

    public function test_a_teacher_adds_edits_and_removes_modules_of_their_course(): void
    {
        $guru = Teacher::factory()->create();
        $kursus = Course::factory()->for($guru)->create();

        foreach (['Pengantar', 'Limit', 'Turunan'] as $judul) {
            $this->actingAs($guru, 'teacher')
                ->postJson(route('api.v1.guru.kelas.modul.store', $kursus), [
                    'title' => $judul,
                    'url' => 'https://drive.google.com/file/d/contoh',
                ])->assertCreated();
        }

        $this->assertSame([1, 2, 3], $kursus->modules()->pluck('number')->all());

        $limit = $kursus->modules()->where('title', 'Limit')->first();

        $this->actingAs($guru, 'teacher')
            ->putJson(route('api.v1.guru.modul.update', $limit), [
                'title' => 'Limit Fungsi',
                'description' => 'Bab 2',
                'url' => null,
            ])->assertOk()
            ->assertJsonPath('title', 'Limit Fungsi')
            ->assertJsonPath('url', null);

        $this->actingAs($guru, 'teacher')
            ->deleteJson(route('api.v1.guru.modul.destroy', $kursus->modules()->where('number', 1)->first()))
            ->assertOk();

        // Nomor dirapatkan: tidak ada celah setelah modul pertama dihapus.
        $this->assertSame(
            [1 => 'Limit Fungsi', 2 => 'Turunan'],
            $kursus->modules()->pluck('title', 'number')->all(),
        );
    }

    public function test_only_http_links_are_accepted(): void
    {
        $guru = Teacher::factory()->create();
        $kursus = Course::factory()->for($guru)->create();

        $this->actingAs($guru, 'teacher')
            ->postJson(route('api.v1.guru.kelas.modul.store', $kursus), [
                'title' => 'Jebakan',
                'url' => 'javascript:alert(1)',
            ])->assertStatus(422)
            ->assertJsonPath('errors.url.0', 'Kolom tautan harus berupa tautan yang sah, diawali http:// atau https://.');
    }

    public function test_another_teachers_course_and_modules_are_not_found(): void
    {
        $guru = Teacher::factory()->create();
        $lain = Course::factory()->create();
        $modul = CourseModule::factory()->for($lain)->create();

        $this->actingAs($guru, 'teacher')
            ->postJson(route('api.v1.guru.kelas.modul.store', $lain), ['title' => 'Susupan'])
            ->assertNotFound();
        $this->actingAs($guru, 'teacher')
            ->putJson(route('api.v1.guru.modul.update', $modul), ['title' => 'Susupan'])
            ->assertNotFound();
        $this->actingAs($guru, 'teacher')
            ->deleteJson(route('api.v1.guru.modul.destroy', $modul))
            ->assertNotFound();

        $this->assertModelExists($modul);
    }

    public function test_grading_covers_classes_from_both_schedules_and_courses(): void
    {
        $guru = Teacher::factory()->create();
        $mtk = Subject::factory()->create(['name' => 'Matematika']);
        $kelasA = Classroom::factory()->create(['name' => 'X-A']);
        $kelasB = Classroom::factory()->create(['name' => 'X-B']);

        Schedule::factory()->for($guru)->for($mtk)->for($kelasA)->create();
        Schedule::factory()->for($guru)->for($mtk)->for($kelasA)->create(); // pasangan sama, satu entri
        Course::factory()->for($guru)->for($mtk)->for($kelasB)->create();

        $this->actingAs($guru, 'teacher')
            ->getJson(route('api.v1.guru.nilai.index'))
            ->assertOk()
            ->assertJsonCount(2, 'classes')
            ->assertJsonPath('classes.0.classroom', 'X-A')
            ->assertJsonPath('classes.1.classroom', 'X-B')
            ->assertJsonPath('selected.classroom', 'X-A');
    }

    public function test_scores_are_saved_edited_and_cleared_without_duplicates(): void
    {
        [$guru, $kunci, $mapel, $siswa] = $this->kelasDenganSiswa();

        $kirim = fn (array $nilai) => $this->actingAs($guru, 'teacher')
            ->postJson(route('api.v1.guru.nilai.store'), [
                'kelas' => $kunci,
                'title' => 'Ulangan Harian 1',
                'assessed_on' => now()->toDateString(),
                'scores' => $nilai,
            ]);

        $kirim([
            ['student_id' => $siswa[0]->id, 'score' => 80],
            ['student_id' => $siswa[1]->id, 'score' => 90],
        ])->assertOk()->assertJsonPath('saved', 2);

        $kirim([
            ['student_id' => $siswa[0]->id, 'score' => 85],
            ['student_id' => $siswa[1]->id, 'score' => null],
        ])->assertOk()->assertJsonPath('saved', 1)->assertJsonPath('removed', 1);

        $this->assertSame(
            [$siswa[0]->id => 85],
            Assessment::query()->where('subject_id', $mapel->id)->pluck('score', 'student_id')->all(),
        );

        // Langsung muncul di rapor siswa.
        ReportCard::factory()->for($siswa[0])->create();
        $this->actingAs($siswa[0], 'student')
            ->getJson(route('api.v1.report-card'))
            ->assertJsonPath('recent_assessments.0.score', 85);
    }

    public function test_scores_for_students_outside_the_class_are_rejected(): void
    {
        [$guru, $kunci] = $this->kelasDenganSiswa();
        $orangLain = Student::factory()->create();

        $this->actingAs($guru, 'teacher')
            ->postJson(route('api.v1.guru.nilai.store'), [
                'kelas' => $kunci,
                'title' => 'Susupan',
                'assessed_on' => now()->toDateString(),
                'scores' => [['student_id' => $orangLain->id, 'score' => 100]],
            ])->assertStatus(422)->assertJsonValidationErrors('scores');

        $this->assertSame(0, Assessment::query()->count());
    }

    public function test_a_class_the_teacher_does_not_teach_is_rejected(): void
    {
        [$guru] = $this->kelasDenganSiswa();
        $lain = Schedule::factory()->create();

        $this->actingAs($guru, 'teacher')
            ->postJson(route('api.v1.guru.nilai.store'), [
                'kelas' => $lain->subject_id.'-'.$lain->classroom_id,
                'title' => 'Susupan',
                'assessed_on' => now()->toDateString(),
                'scores' => [],
            ])->assertStatus(422)
            ->assertJsonPath('errors.kelas.0', 'Kelas ini bukan kelas yang Anda ampu.');
    }

    /** @return array{Teacher, string, Subject, Collection<int, Student>} */
    private function kelasDenganSiswa(): array
    {
        $guru = Teacher::factory()->create();
        $mapel = Subject::factory()->create();
        $kelas = Classroom::factory()->create();
        $siswa = Student::factory()->count(2)->for($kelas)->create();
        Schedule::factory()->for($guru)->for($mapel)->for($kelas)->create();

        return [$guru, $mapel->id.'-'.$kelas->id, $mapel, $siswa];
    }
}
