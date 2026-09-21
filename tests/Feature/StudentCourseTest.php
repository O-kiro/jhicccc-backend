<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
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

    public function test_progress_belongs_to_the_signed_in_student_not_a_classmate(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();
        $classmate = Student::factory()->for($classroom)->create();
        $course = Course::factory()->for($classroom)->create();

        Enrollment::factory()->for($student)->for($course)->create(['progress_percentage' => 30]);
        Enrollment::factory()->for($classmate)->for($course)->create(['progress_percentage' => 90]);

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.courses'));

        $response->assertOk();
        $this->assertSame(30, $response->json('courses.0.progress'));
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
     * Angka di kartu harus berasal dari modul yang benar-benar ada, bukan
     * kolom courses.module_count — kalau tidak, kartu bisa menjanjikan 12
     * modul sementara daftarnya hanya berisi 2.
     */
    public function test_the_module_count_matches_the_modules_that_exist(): void
    {
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->for($classroom)->create();
        $course = Course::factory()->for($classroom)->create(['module_count' => 12]);

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
