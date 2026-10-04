<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\Task;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TugasTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_membuat_tugas_dan_siswa_di_kelasnya_melihatnya(): void
    {
        $guru = Teacher::factory()->create();
        $kursus = Course::factory()->for($guru)->create();
        $siswa = Student::factory()->create(['classroom_id' => $kursus->classroom_id]);
        $lain = Student::factory()->create();

        $this->actingAs($guru, 'teacher')->postJson(route('api.v1.guru.tugas.store'), [
            'course_id' => $kursus->id,
            'title' => 'Rangkum bab 3',
            'description' => 'Tulis tangan, 1 halaman.',
            'due_at' => '2026-10-10T23:59',
        ])->assertCreated()->assertJsonPath('title', 'Rangkum bab 3');

        $this->actingAs($siswa, 'student')->getJson(route('api.v1.tugas.index'))
            ->assertOk()
            ->assertJsonCount(1, 'tasks')
            ->assertJsonPath('tasks.0.title', 'Rangkum bab 3')
            ->assertJsonPath('tasks.0.completed', false);

        $this->actingAs($lain, 'student')->getJson(route('api.v1.tugas.index'))
            ->assertJsonCount(0, 'tasks');
    }

    public function test_guru_tidak_bisa_memberi_tugas_ke_kursus_guru_lain(): void
    {
        $guru = Teacher::factory()->create();
        $kursusOrang = Course::factory()->create();

        $this->actingAs($guru, 'teacher')->postJson(route('api.v1.guru.tugas.store'), [
            'course_id' => $kursusOrang->id,
            'title' => 'Iseng',
            'due_at' => '2026-10-10T23:59',
        ])->assertNotFound();

        $tugas = Task::factory()->for($kursusOrang)->create();
        $this->actingAs($guru, 'teacher')->deleteJson(route('api.v1.guru.tugas.destroy', $tugas))->assertNotFound();
        $this->assertModelExists($tugas);
    }

    public function test_siswa_menandai_selesai_dan_angka_dashboard_berkurang(): void
    {
        $kursus = Course::factory()->create();
        $siswa = Student::factory()->create(['classroom_id' => $kursus->classroom_id]);
        $tugas = Task::factory()->count(2)->for($kursus)->create();

        $this->actingAs($siswa, 'student')->getJson(route('api.v1.overview'))
            ->assertJsonPath('summary.active_tasks', 2);

        $this->actingAs($siswa, 'student')->postJson(route('api.v1.tugas.toggle', $tugas[0]))
            ->assertOk()->assertJsonPath('completed', true);

        $this->actingAs($siswa, 'student')->getJson(route('api.v1.overview'))
            ->assertJsonPath('summary.active_tasks', 1);

        $guru = $kursus->teacher;
        $this->actingAs($guru, 'teacher')->getJson(route('api.v1.guru.tugas.index'))
            ->assertOk()
            ->assertJsonCount(2, 'tasks')
            ->assertJsonPath('courses.0.id', $kursus->id);
    }

    public function test_siswa_tidak_bisa_menandai_tugas_kelas_lain(): void
    {
        $tugas = Task::factory()->create();

        $this->actingAs(Student::factory()->create(), 'student')
            ->postJson(route('api.v1.tugas.toggle', $tugas))
            ->assertNotFound();
    }
}
