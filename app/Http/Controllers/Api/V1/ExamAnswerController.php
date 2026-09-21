<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ExamAnswer;
use App\Models\ExamQuestion;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExamAnswerController extends Controller
{
    /**
     * Menyimpan satu jawaban (atau penanda ragu-ragu) dari sesi CBT berjalan.
     *
     * Dipanggil tiap kali siswa memilih opsi, jadi disengaja bersifat
     * idempoten: satu baris per (soal, siswa), ditimpa kalau sudah ada.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'choice' => ['nullable', 'string', 'max:2'],
            'flagged' => ['required', 'boolean'],
        ]);

        // Soal harus milik ujian yang sedang berlangsung untuk kelas siswa
        // ini. Tanpa pemeriksaan ini, siswa bisa menulis jawaban ke ujian
        // kelas lain, atau ke ujian yang waktunya sudah habis.
        $question = ExamQuestion::query()
            ->whereKey($data['question_id'])
            ->whereHas('exam', function ($q) use ($student): void {
                $q->where('classroom_id', $student->classroom_id)
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now())
                    // Sesi yang sudah dikirim tidak menerima perubahan lagi.
                    ->whereDoesntHave('results', fn ($r) => $r->where('student_id', $student->id));
            })
            ->first();

        if (! $question) {
            throw new NotFoundHttpException('Soal tidak ada dalam sesi ujian yang sedang berlangsung.');
        }

        // Pilihan harus salah satu opsi milik soal itu — bukan huruf sembarang.
        if ($data['choice'] !== null) {
            $request->validate([
                'choice' => [Rule::in($question->options()->pluck('key')->all())],
            ]);
        }

        ExamAnswer::query()->updateOrCreate(
            ['exam_question_id' => $question->id, 'student_id' => $student->id],
            ['choice' => $data['choice'], 'is_flagged' => $data['flagged']],
        );

        return response()->json(['saved' => true]);
    }
}
