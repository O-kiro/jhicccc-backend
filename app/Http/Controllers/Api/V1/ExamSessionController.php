<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ExamQuestionResource;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExamSessionController extends Controller
{
    /**
     * Sesi CBT yang sedang berlangsung untuk kelas siswa.
     *
     * Soal dikirim lengkap agar navigator 40 nomor bisa berpindah tanpa
     * permintaan baru; kunci jawaban tidak ikut — lihat ExamQuestionResource.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $exam = Exam::query()
            ->where('classroom_id', $student->classroom_id)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            // Ujian yang sudah dikirim tidak boleh dibuka lagi, walau waktunya
            // masih tersisa — kalau tidak, nilainya bisa diperbaiki berulang.
            ->whereDoesntHave('results', fn ($q) => $q->where('student_id', $student->id))
            ->with(['subject', 'questions.options'])
            ->orderBy('starts_at')
            ->first();

        if (! $exam) {
            throw new NotFoundHttpException('Tidak ada sesi ujian yang sedang berlangsung.');
        }

        // Dihitung server agar sisa waktu tidak bisa dimundurkan lewat jam
        // perangkat siswa.
        $remaining = max(0, (int) now()->diffInSeconds($exam->ends_at, false));

        $answers = $student->examAnswers()
            ->whereIn('exam_question_id', $exam->questions->pluck('id'))
            ->get()
            ->keyBy('exam_question_id');

        return response()->json([
            'exam' => [
                'id' => $exam->id,
                'subject' => $exam->subject->name,
                'title' => $exam->title,
                'total_questions' => $exam->questions->count(),
                'remaining_seconds' => $remaining,
            ],
            'questions' => ExamQuestionResource::collection($exam->questions),
            // Jawaban yang sudah tersimpan, sehingga sesi bisa dilanjutkan
            // setelah halaman ditutup. Ditulis oleh ExamAnswerController.
            'answers' => $exam->questions->mapWithKeys(fn ($q): array => [
                $q->number => [
                    'choice' => $answers[$q->id]->choice ?? null,
                    'flagged' => (bool) ($answers[$q->id]->is_flagged ?? false),
                ],
            ]),
        ]);
    }
}
