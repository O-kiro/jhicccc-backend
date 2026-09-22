<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\ModuleCompletion;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    /**
     * Kursus yang diampu beserta modulnya dan seberapa jauh siswa terdaftar
     * mengikutinya. Progres dihitung dengan cara yang sama seperti di portal
     * siswa: modul yang ditandai selesai dibagi jumlah modul.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $kursus = $guru->courses()
            ->with(['subject', 'classroom', 'modules', 'enrollments:id,course_id,student_id'])
            ->get()
            ->sortBy([['classroom.name', 'asc'], ['subject.name', 'asc']])
            ->values();

        return response()->json([
            'courses' => $kursus->map(function (Course $c): array {
                $siswa = $c->enrollments->pluck('student_id');

                // Hanya siswa yang terdaftar: siswa yang sudah keluar dari
                // kursus tidak boleh menarik rata-rata progres.
                $selesai = ModuleCompletion::query()
                    ->whereIn('course_module_id', $c->modules->modelKeys())
                    ->whereIn('student_id', $siswa)
                    ->selectRaw('course_module_id, COUNT(*) as total')
                    ->groupBy('course_module_id')
                    ->pluck('total', 'course_module_id');

                $slot = $c->modules->count() * $siswa->count();

                return [
                    'id' => $c->id,
                    'subject' => $c->subject->name,
                    'category' => $c->subject->category,
                    'icon' => $c->subject->icon,
                    'tone' => $c->subject->tone,
                    'classroom' => $c->classroom?->name,
                    'academic_year' => $c->academic_year,
                    'semester' => $c->semester,
                    'students' => $siswa->count(),
                    'average_progress' => $slot > 0 ? (int) round($selesai->sum() / $slot * 100) : 0,
                    'modules' => $c->modules->map(fn (CourseModule $m): array => [
                        ...ModulController::bentuk($m),
                        'completed' => (int) ($selesai[$m->id] ?? 0),
                    ]),
                ];
            }),
        ]);
    }
}
