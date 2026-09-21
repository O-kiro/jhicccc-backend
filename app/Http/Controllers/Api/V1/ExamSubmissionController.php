<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExamSubmissionController extends Controller
{
    /**
     * Mengakhiri sesi CBT dan menilainya.
     *
     * Penilaian dikerjakan di sini, bukan di klien: kunci jawaban tidak pernah
     * meninggalkan server, jadi hanya server yang bisa menghitung nilainya.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        // Ujian yang sudah lewat tetap boleh dikirim — kalau tidak, siswa yang
        // kehabisan waktu saat menekan "Selesai" kehilangan seluruh jawabannya.
        // Yang menentukan adalah ujian itu pernah dibuka untuk kelasnya.
        $exam = Exam::query()
            ->where('classroom_id', $student->classroom_id)
            ->where('starts_at', '<=', now())
            ->with('questions.options')
            ->orderByDesc('starts_at')
            ->first();

        if (! $exam) {
            throw new NotFoundHttpException('Tidak ada sesi ujian yang bisa diakhiri.');
        }

        $total = $exam->questions->count();

        if ($total === 0) {
            throw new NotFoundHttpException('Ujian ini belum memiliki soal.');
        }

        $jawaban = $student->examAnswers()
            ->whereIn('exam_question_id', $exam->questions->pluck('id'))
            ->pluck('choice', 'exam_question_id');

        $benar = $exam->questions->filter(function ($soal) use ($jawaban): bool {
            $pilihan = $jawaban[$soal->id] ?? null;

            return $pilihan !== null
                && $soal->options->firstWhere('key', $pilihan)?->is_correct === true;
        })->count();

        $nilai = (int) round($benar / $total * 100);

        // updateOrCreate, bukan create: batasan unik (exam_id, student_id)
        // mencegah nilai ganda, dan mengirim ulang tidak boleh melempar galat.
        $hasil = DB::transaction(fn (): ExamResult => ExamResult::query()->updateOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $student->id],
            ['score' => $nilai, 'finished_at' => now()],
        ));

        return response()->json([
            'exam_id' => $exam->id,
            'subject' => $exam->subject->name,
            'title' => $exam->title,
            'score' => $hasil->score,
            'correct' => $benar,
            'total_questions' => $total,
            'finished_on' => $hasil->finished_at?->toDateString(),
        ]);
    }
}
