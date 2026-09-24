<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ReportUpload;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherFeedback;
use App\Support\Pengampuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * RDM: unggah berkas rapor per kelas, dan catatan guru untuk siswa.
 *
 * Catatannya memakai tabel teacher_feedback yang sama dengan "Catatan Guru"
 * di Rapor Digital siswa — jadi yang ditulis di sini langsung terbaca siswa.
 */
class RdmController extends Controller
{
    /** Batas ukuran berkas rapor, dalam kilobita. */
    private const MAKS_BERKAS_KB = 15360;

    public function index(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $idKelas = Pengampuan::kelas($guru);

        $berkas = ReportUpload::query()
            ->with('classroom')
            ->where('teacher_id', $guru->id)
            ->latest('id')
            ->limit(30)
            ->get();

        $catatan = TeacherFeedback::query()
            ->with('student')
            ->where('teacher_id', $guru->id)
            ->latest('id')
            ->limit(30)
            ->get();

        return response()->json([
            'classrooms' => Classroom::query()
                ->whereIn('id', $idKelas)
                ->orderBy('name')
                ->get(['id', 'name', 'academic_year']),
            'students' => Student::query()
                ->whereIn('classroom_id', $idKelas)
                ->where('is_active', true)
                ->with('classroom:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'classroom_id'])
                ->map(fn (Student $s): array => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'classroom' => $s->classroom?->name,
                ]),
            'uploads' => $berkas->map(fn (ReportUpload $b): array => [
                'id' => $b->id,
                'classroom' => $b->classroom?->name,
                'academic_year' => $b->academic_year,
                'semester' => $b->semester,
                'file_url' => $b->fileUrl(),
                'original_name' => $b->original_name,
                'size_kb' => $b->size_kb,
                'note' => $b->note,
                'uploaded_on' => $b->created_at?->toDateString(),
            ]),
            'feedback' => $catatan->map(fn (TeacherFeedback $c): array => [
                'id' => $c->id,
                'student' => $c->student->name,
                'student_id' => $c->student_id,
                'role' => $c->role,
                'body' => $c->body,
                'created_on' => $c->created_at?->toDateString(),
            ]),
            'max_file_kb' => self::MAKS_BERKAS_KB,
            'semesters' => ['Ganjil', 'Genap'],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $data = $request->validate([
            'classroom_id' => ['required', 'integer', Rule::in(Pengampuan::kelas($guru))],
            'academic_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            // PDF dan Excel saja; berkas rapor dari aplikasi RDM berbentuk itu.
            'file' => ['required', 'file', 'mimes:pdf,xlsx,xls', 'max:'.self::MAKS_BERKAS_KB],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'classroom_id.in' => 'Kelas ini bukan kelas yang Anda ampu.',
            'file.max' => 'Berkas terlalu besar. Maksimal 15 MB.',
        ]);

        $berkas = $request->file('file');

        $unggahan = ReportUpload::query()->create([
            'teacher_id' => $guru->id,
            'classroom_id' => $data['classroom_id'],
            'academic_year' => $data['academic_year'],
            'semester' => $data['semester'],
            'file_path' => $berkas->store('guru/rdm', 'public'),
            'original_name' => $berkas->getClientOriginalName(),
            'size_kb' => (int) ceil($berkas->getSize() / 1024),
            'note' => $data['note'] ?? null,
        ]);

        return response()->json(['id' => $unggahan->id, 'file_url' => $unggahan->fileUrl()], 201);
    }

    public function destroy(Request $request, ReportUpload $upload): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        if ($upload->teacher_id !== $guru->id) {
            throw new NotFoundHttpException('Berkas tidak ditemukan.');
        }

        // Berkasnya ikut terhapus lewat MembersihkanGambarUnggahan.
        $upload->delete();

        return response()->json(['deleted' => true]);
    }

    public function storeCatatan(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $data = $this->validasiCatatan($request, $guru);

        $catatan = TeacherFeedback::query()->create([
            ...$data,
            'teacher_id' => $guru->id,
        ]);

        return response()->json(['id' => $catatan->id], 201);
    }

    public function updateCatatan(Request $request, TeacherFeedback $feedback): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $this->pastikanCatatanSendiri($feedback, $guru);
        $feedback->update($this->validasiCatatan($request, $guru));

        return response()->json(['id' => $feedback->id]);
    }

    public function destroyCatatan(Request $request, TeacherFeedback $feedback): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $this->pastikanCatatanSendiri($feedback, $guru);
        $feedback->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validasiCatatan(Request $request, Teacher $guru): array
    {
        $idSiswa = Student::query()
            ->whereIn('classroom_id', Pengampuan::kelas($guru))
            ->pluck('id')
            ->all();

        return $request->validate([
            'student_id' => ['required', 'integer', Rule::in($idSiswa)],
            'role' => ['required', 'string', 'max:50'],
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'student_id.in' => 'Siswa ini bukan siswa di kelas yang Anda ampu.',
        ]);
    }

    private function pastikanCatatanSendiri(TeacherFeedback $feedback, Teacher $guru): void
    {
        if ($feedback->teacher_id !== $guru->id) {
            throw new NotFoundHttpException('Catatan tidak ditemukan.');
        }
    }
}
