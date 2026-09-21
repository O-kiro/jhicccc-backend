<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CourseResource;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Mata pelajaran untuk kelas siswa yang sedang masuk, beserta progres
     * modulnya sendiri.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $courses = Course::query()
            ->where('classroom_id', $student->classroom_id)
            ->with([
                'subject',
                'teacher',
                // Dibatasi ke siswa ini: yang dibutuhkan hanya "sudah selesai
                // atau belum", bukan catatan teman sekelas.
                'modules.completions' => fn ($q) => $q->where('student_id', $student->id),
            ])
            ->get()
            ->sortBy(fn (Course $c): string => $c->subject->name)
            ->values();

        $semester = $courses->first();

        return response()->json([
            'semester' => $semester
                ? "Semester {$semester->semester} {$semester->academic_year}"
                : $student->classroom?->academic_year,
            'filters' => ['All', 'Agama', 'Sains'],
            'courses' => CourseResource::collection($courses),
        ]);
    }
}
