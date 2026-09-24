<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\LessonPlan;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Modul ajar (TP/ATP). Modul guru lain ikut terlihat — sesuai desain, guru
 * saling meminjam perangkat pembelajaran — tapi hanya pemiliknya yang boleh
 * mengubah atau menghapus.
 */
class ModulAjarController extends Controller
{
    private const TAB = ['saya', 'rekan', 'arsip'];

    public function index(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $tab = in_array($request->query('tab'), self::TAB, true) ? $request->query('tab') : 'saya';
        $mapel = $request->integer('subject_id') ?: null;
        $kelas = $request->integer('classroom_id') ?: null;

        $dasar = fn () => LessonPlan::query()->with(['subject', 'classroom', 'teacher']);

        $daftar = $dasar()
            ->when($tab === 'saya', fn ($q) => $q->where('teacher_id', $guru->id)->where('status', 'aktif'))
            ->when($tab === 'rekan', fn ($q) => $q->where('teacher_id', '!=', $guru->id)->where('status', 'aktif'))
            ->when($tab === 'arsip', fn ($q) => $q->where('teacher_id', $guru->id)->where('status', 'arsip'))
            ->when($mapel, fn ($q) => $q->where('subject_id', $mapel))
            ->when($kelas, fn ($q) => $q->where('classroom_id', $kelas))
            ->latest('id')
            ->limit(100)
            ->get();

        return response()->json([
            'tab' => $tab,
            'filters' => ['subject_id' => $mapel, 'classroom_id' => $kelas],
            'counts' => [
                'saya' => LessonPlan::query()->where('teacher_id', $guru->id)->where('status', 'aktif')->count(),
                'rekan' => LessonPlan::query()->where('teacher_id', '!=', $guru->id)->where('status', 'aktif')->count(),
                'arsip' => LessonPlan::query()->where('teacher_id', $guru->id)->where('status', 'arsip')->count(),
            ],
            'plans' => $daftar->map(fn (LessonPlan $m): array => [
                'id' => $m->id,
                'title' => $m->title,
                'subject' => $m->subject?->name,
                'classroom' => $m->classroom?->name,
                'time_range' => $m->time_range,
                'url' => $m->url,
                'note' => $m->note,
                'status' => $m->status,
                'teacher' => $m->teacher->name,
                'is_mine' => $m->teacher_id === $guru->id,
            ]),
            'subjects' => Subject::query()->orderBy('name')->get(['id', 'name']),
            'classrooms' => Classroom::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $modul = LessonPlan::query()->create([
            ...$this->validasi($request),
            'teacher_id' => $guru->id,
        ]);

        return response()->json(['id' => $modul->id], 201);
    }

    public function update(Request $request, LessonPlan $plan): JsonResponse
    {
        $this->pastikanMilikSendiri($request, $plan);

        $plan->update($this->validasi($request));

        return response()->json(['id' => $plan->id]);
    }

    public function destroy(Request $request, LessonPlan $plan): JsonResponse
    {
        $this->pastikanMilikSendiri($request, $plan);

        $plan->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'classroom_id' => ['nullable', 'integer', Rule::exists('classrooms', 'id')],
            'time_range' => ['nullable', 'string', 'max:50'],
            // Hanya http(s): tautan ini dibuka guru lain.
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'note' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', Rule::in(array_keys(LessonPlan::STATUS))],
        ]);

        return [
            'title' => $data['title'],
            'subject_id' => $data['subject_id'] ?? null,
            'classroom_id' => $data['classroom_id'] ?? null,
            'time_range' => $data['time_range'] ?? null,
            'url' => $data['url'] ?? null,
            'note' => $data['note'] ?? null,
            'status' => $data['status'] ?? 'aktif',
        ];
    }

    /** 404, bukan 403: modul guru lain boleh dilihat, tapi bukan urusannya. */
    private function pastikanMilikSendiri(Request $request, LessonPlan $plan): void
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        if ($plan->teacher_id !== $guru->id) {
            throw new NotFoundHttpException('Modul ajar tidak ditemukan.');
        }
    }
}
