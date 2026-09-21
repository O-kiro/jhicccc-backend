<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\ModuleCompletion;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_only_courses_from_the_students_own_classroom(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();

        $mine = Course::factory()->for($classroom)->create();
        Course::factory()->create(); // kelas lain

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.courses'));

        $response->assertOk()->assertJsonCount(1, 'courses');
        $this->assertSame($mine->id, $response->json('courses.0.id'));
    }

    /** Progres = modul yang ditandai selesai oleh siswa ini, bukan teman sekelasnya. */
    public function test_progress_counts_only_the_students_own_completed_modules(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();
        $classmate = Student::factory()->for($classroom)->create();
        $course = Course::factory()->for($classroom)->create();

        $modul = CourseModule::factory()->count(4)->for($course)
            ->sequence(fn ($s) => ['number' => $s->index + 1])->create();

        ModuleCompletion::query()->create(['student_id' => $student->id, 'course_module_id' => $modul[0]->id, 'completed_at' => now()]);
        foreach ($modul as $m) {
            ModuleCompletion::query()->create(['student_id' => $classmate->id, 'course_module_id' => $m->id, 'completed_at' => now()]);
        }

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.courses'));

        $response->assertOk()
            ->assertJsonPath('courses.0.progress', 25)
            ->assertJsonPath('courses.0.module_list.0.completed', true)
            ->assertJsonPath('courses.0.module_list.1.completed', false);
    }

    public function test_toggling_a_module_updates_progress_both_ways(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();
        $course = Course::factory()->for($classroom)->create();
        $modul = CourseModule::factory()->count(2)->for($course)
            ->sequence(fn ($s) => ['number' => $s->index + 1])->create();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.courses.modules.toggle', $modul[0]))
            ->assertOk()
            ->assertJsonPath('completed', true)
            ->assertJsonPath('progress', 50);

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.courses.modules.toggle', $modul[0]))
            ->assertOk()
            ->assertJsonPath('completed', false)
            ->assertJsonPath('progress', 0);
    }

    public function test_a_module_from_another_classroom_cannot_be_marked(): void
    {
        $student = Student::factory()->create();
        $asing = CourseModule::factory()->create();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.courses.modules.toggle', $asing))
            ->assertNotFound();

        $this->assertSame(0, ModuleCompletion::query()->count());
    }

    public function test_a_course_without_modules_has_zero_progress(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();
        Course::factory()->for($classroom)->create();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.courses'))
            ->assertJsonPath('courses.0.progress', 0);
    }

    public function test_it_requires_authentication(): void
    {
        $this->getJson(route('api.v1.courses'))->assertUnauthorized();
    }

    public function test_it_lists_the_modules_of_each_course_in_order(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();
        $course = Course::factory()->for($classroom)->create();

        CourseModule::factory()->for($course)->create(['number' => 2, 'title' => 'Kedua']);
        CourseModule::factory()->for($course)->linked()->create(['number' => 1, 'title' => 'Pertama']);

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.courses'));

        $response->assertOk()
            ->assertJsonCount(2, 'courses.0.module_list')
            ->assertJsonPath('courses.0.module_list.0.title', 'Pertama')
            ->assertJsonPath('courses.0.module_list.1.title', 'Kedua');

        $this->assertNotNull($response->json('courses.0.module_list.0.url'));
        $this->assertNull($response->json('courses.0.module_list.1.url'));
    }

    /**
     * Angka di kartu harus sama dengan panjang daftar modulnya. Dulu ada kolom
     * courses.module_count yang disimpan terpisah; kolom itu sudah dibuang
     * justru karena bisa menjanjikan 12 modul sementara isinya hanya 2.
     */
    public function test_the_module_count_matches_the_modules_that_exist(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();
        $course = Course::factory()->for($classroom)->create();

        CourseModule::factory()->count(2)->for($course)->sequence(
            ['number' => 1],
            ['number' => 2],
        )->create();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.courses'))
            ->assertOk()
            ->assertJsonPath('courses.0.modules', 2);
    }
}
