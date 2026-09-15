<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AssessmentResource;
use App\Http\Resources\V1\ReportCardResource;
use App\Http\Resources\V1\TeacherFeedbackResource;
use App\Models\Assessment;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportCardController extends Controller
{
    /**
     * Rapor Digital Madrasah untuk siswa yang sedang masuk: ringkasan,
     * sejarah nilai bulanan, penilaian terbaru, dan catatan guru.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $reportCard = $student->reportCards()
            ->when(
                $request->filled('semester'),
                fn ($query) => $query->where('semester', $request->string('semester')),
            )
            ->when(
                $request->filled('academic_year'),
                fn ($query) => $query->where('academic_year', $request->string('academic_year')),
            )
            ->latest('id')
            ->first();

        if (! $reportCard) {
            throw new NotFoundHttpException('Rapor untuk periode ini belum tersedia.');
        }

        $assessments = $student->assessments()->with('subject')->get();

        return response()->json([
            'report_card' => new ReportCardResource($reportCard),
            'grade_history' => $this->monthlyAverages($assessments),
            'recent_assessments' => AssessmentResource::collection(
                $assessments->sortByDesc('assessed_on')->take(3)->values(),
            ),
            'teacher_feedback' => TeacherFeedbackResource::collection(
                $student->teacherFeedback()->with('teacher')->latest()->get(),
            ),
        ]);
    }

    /**
     * Rata-rata nilai per bulan untuk grafik "Sejarah Nilai".
     *
     * @param  Collection<int, Assessment>  $assessments
     * @return array<int, array{month: string, score: float}>
     */
    private function monthlyAverages($assessments): array
    {
        return $assessments
            ->groupBy(fn ($assessment) => $assessment->assessed_on->format('Y-m'))
            ->sortKeys()
            ->map(fn ($group, $month) => [
                'month' => $group->first()->assessed_on->translatedFormat('M'),
                'score' => round($group->avg('score'), 1),
            ])
            ->values()
            ->all();
    }
}
