<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\ModuleCompletion;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ModuleCompletionController extends Controller
{
    /**
     * Menandai modul selesai, atau membatalkannya bila sudah ditandai.
     * Mengembalikan progres kursus yang baru supaya kartu langsung benar.
     */
    public function toggle(Request $request, CourseModule $module): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();
        $course = $module->course;

        // Hanya modul dari kursus di kelasnya sendiri.
        if (! $course || $course->classroom_id !== $student->classroom_id) {
            throw new NotFoundHttpException('Modul tidak ditemukan.');
        }

        $ada = ModuleCompletion::query()
            ->where('student_id', $student->id)
            ->where('course_module_id', $module->id)
            ->first();

        if ($ada) {
            $ada->delete();
        } else {
            ModuleCompletion::query()->create([
                'student_id' => $student->id,
                'course_module_id' => $module->id,
                'completed_at' => now(),
            ]);
        }

        $total = $course->modules()->count();
        $selesai = ModuleCompletion::query()
            ->where('student_id', $student->id)
            ->whereIn('course_module_id', $course->modules()->select('id'))
            ->count();

        return response()->json([
            'completed' => ! $ada,
            'progress' => $total > 0 ? (int) round($selesai / $total * 100) : 0,
        ]);
    }
}
