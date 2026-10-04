<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Task;
use App\Models\TaskCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** Tugas di portal siswa: daftar dari semua mapel di kelasnya, dan tanda selesai. */
class TugasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $tugas = Task::query()
            ->untukSiswa($student)
            ->with(['course.subject', 'course.teacher'])
            ->withExists(['completions as selesai' => fn ($q) => $q->where('student_id', $student->id)])
            ->orderBy('due_at')
            ->get();

        return response()->json([
            'tasks' => $tugas->map(fn (Task $t): array => [
                'id' => $t->id,
                'title' => $t->title,
                'description' => $t->description,
                'url' => $t->url,
                'due_at' => $t->due_at->toIso8601String(),
                'overdue' => ! $t->selesai && $t->due_at->isPast(),
                'completed' => (bool) $t->selesai,
                'subject' => $t->course->subject?->name,
                'tone' => $t->course->subject?->tone ?? 'blue',
                'teacher' => $t->course->teacher?->name,
            ]),
        ]);
    }

    public function toggle(Request $request, Task $task): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        if ($task->course?->classroom_id !== $student->classroom_id) {
            throw new NotFoundHttpException('Tugas tidak ditemukan.');
        }

        $ada = TaskCompletion::query()->where('task_id', $task->id)->where('student_id', $student->id)->first();

        if ($ada) {
            $ada->delete();
        } else {
            TaskCompletion::query()->create([
                'task_id' => $task->id,
                'student_id' => $student->id,
                'completed_at' => now(),
            ]);
        }

        return response()->json(['completed' => ! $ada]);
    }
}
