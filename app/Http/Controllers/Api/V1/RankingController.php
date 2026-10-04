<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ReportUpload;
use App\Models\Student;
use App\Models\TeacherFeedback;
use App\Support\BacaXlsx;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Halaman Ranking portal siswa: isi Excel ranking terbaru yang ditandai guru
 * untuk kelas siswa ini (lewat RDM), dan catatan guru untuk siswa ini.
 */
class RankingController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $berkas = ReportUpload::query()
            ->with('teacher')
            ->where('classroom_id', $student->classroom_id)
            ->where('shown_to_students', true)
            ->latest('id')
            ->first();

        $catatan = TeacherFeedback::query()
            ->with('teacher')
            ->where('student_id', $student->id)
            ->latest('id')
            ->limit(20)
            ->get();

        return response()->json([
            'sheet' => $berkas ? [
                'title' => 'Ranking Kelas '.($student->classroom?->name ?? ''),
                'academic_year' => $berkas->academic_year,
                'semester' => $berkas->semester,
                'teacher' => $berkas->teacher?->name,
                'uploaded_on' => $berkas->created_at?->toDateString(),
                'note' => $berkas->note,
                'rows' => Storage::disk('public')->exists($berkas->file_path)
                    ? BacaXlsx::lembarPertama(Storage::disk('public')->path($berkas->file_path))
                    : null,
            ] : null,
            'teacher_feedback' => $catatan->map(fn (TeacherFeedback $c): array => [
                'id' => $c->id,
                'name' => $c->teacher?->name ?? 'Guru',
                'photo' => null,
                'role' => $c->role,
                'body' => $c->body,
                'created_at' => $c->created_at?->toIso8601String(),
            ]),
        ]);
    }
}
