<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ExamResource;
use App\Http\Resources\V1\ExamResultResource;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /** Aturan ujian bersifat tetap; belum ada kebutuhan mengubahnya per madrasah. */
    private const RULES = [
        'Harus menggunakan Wifi MAKOBA dan tidak diperbolehkan menggunakan sim card.',
        'Tidak boleh menggunakan tab atau aplikasi lain saat sesi CBT aktif.',
        'Dilarang berganti atau beralih tab.',
    ];

    /**
     * Pusat ujian: yang akan datang untuk kelas siswa, dan hasil miliknya.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $upcoming = Exam::query()
            ->where('classroom_id', $student->classroom_id)
            ->upcoming()
            ->with('subject')
            ->orderBy('starts_at')
            ->get();

        $results = $student->examResults()
            ->with('exam.subject')
            ->latest('finished_at')
            ->get();

        return response()->json([
            'upcoming' => ExamResource::collection($upcoming),
            'results' => ExamResultResource::collection($results),
            'rules' => self::RULES,
        ]);
    }
}
