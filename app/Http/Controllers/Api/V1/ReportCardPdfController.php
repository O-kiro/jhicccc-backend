<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportCardPdfController extends Controller
{
    /**
     * Rapor Digital Madrasah dalam bentuk PDF siap unduh.
     *
     * Dibuat di server, bukan dari cetak halaman: isinya harus sama persis
     * bagi siapa pun yang mengunduh, tidak bergantung pada peramban atau
     * pengaturan cetak masing-masing siswa.
     */
    public function __invoke(Request $request): Response
    {
        /** @var Student $student */
        $student = $request->user();
        $student->load('classroom');

        $reportCard = $student->reportCards()->latest('id')->first();

        if (! $reportCard) {
            throw new NotFoundHttpException('Rapor untuk periode ini belum tersedia.');
        }

        $pdf = Pdf::loadView('pdf.rapor', [
            'student' => $student,
            'reportCard' => $reportCard,
            'assessments' => $student->assessments()->with('subject')->orderByDesc('assessed_on')->get(),
            'feedback' => $student->teacherFeedback()->with('teacher')->latest()->get(),
        ]);

        $nama = Str::slug("rapor {$student->name} {$reportCard->semester} {$reportCard->academic_year}");

        return $pdf->download("{$nama}.pdf");
    }
}
