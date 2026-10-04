<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Task;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Tugas yang diberikan guru untuk kursus yang diampunya. Siswa di kelas
 * kursus itu langsung melihatnya di halaman Tugas portal siswa.
 */
class TugasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $kursus = $guru->courses()->with(['subject', 'classroom'])->get()
            ->sortBy([['classroom.name', 'asc'], ['subject.name', 'asc']])->values();

        $tugas = Task::query()
            ->whereIn('course_id', $kursus->modelKeys())
            ->withCount('completions')
            ->orderByDesc('due_at')
            ->get();

        // Penerima = siswa di kelas kursusnya, sama dengan yang melihat tugas.
        $penerima = $kursus->mapWithKeys(fn (Course $c) => [
            $c->id => $c->classroom ? $c->classroom->students()->count() : 0,
        ]);

        $label = $kursus->mapWithKeys(fn (Course $c) => [
            $c->id => ($c->subject?->name ?? 'Mapel').' · Kelas '.($c->classroom?->name ?? '—'),
        ]);

        return response()->json([
            'courses' => $kursus->map(fn (Course $c): array => ['id' => $c->id, 'label' => $label[$c->id]]),
            'tasks' => $tugas->map(fn (Task $t): array => [
                ...self::bentuk($t),
                'completed' => $t->completions_count,
                'students' => $penerima[$t->course_id] ?? 0,
                'course' => $label[$t->course_id] ?? null,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validasi($request, true);
        $this->pastikanPengampu($request, Course::query()->find($data['course_id']));

        $tugas = Task::query()->create($data);

        return response()->json(self::bentuk($tugas), 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->pastikanPengampu($request, $task->course);

        $task->update($this->validasi($request, false));

        return response()->json(self::bentuk($task));
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->pastikanPengampu($request, $task->course);

        $task->delete();

        return response()->json(['deleted' => true]);
    }

    /** @return array<string, mixed> */
    public static function bentuk(Task $t): array
    {
        return [
            'id' => $t->id,
            'course_id' => $t->course_id,
            'title' => $t->title,
            'description' => $t->description,
            'url' => $t->url,
            'due_at' => $t->due_at->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function validasi(Request $request, bool $baru): array
    {
        $data = $request->validate([
            'course_id' => [$baru ? 'required' : 'prohibited', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            // Hanya http(s): tautan ini dibuka siswa.
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'due_at' => ['required', 'date'],
        ]);

        return array_filter([
            'course_id' => $data['course_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'url' => $data['url'] ?? null,
            // Waktu dari formulir dibaca sebagai jam sekolah (WIB).
            'due_at' => Carbon::parse($data['due_at'], config('app.timezone')),
        ], fn ($v, $k) => $k !== 'course_id' || $v !== null, ARRAY_FILTER_USE_BOTH);
    }

    /** 404, bukan 403: kursus guru lain tidak perlu terlihat ada. */
    private function pastikanPengampu(Request $request, ?Course $course): void
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        if ($course?->teacher_id !== $guru->id) {
            throw new NotFoundHttpException('Kursus tidak ditemukan.');
        }
    }
}
