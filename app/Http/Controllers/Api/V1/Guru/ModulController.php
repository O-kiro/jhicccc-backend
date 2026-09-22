<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Guru mengelola modul kursusnya sendiri — judul, keterangan, dan tautan
 * materi (Drive, YouTube, dsb.) yang langsung tampil di portal siswa.
 */
class ModulController extends Controller
{
    public function store(Request $request, Course $course): JsonResponse
    {
        $this->pastikanPengampu($request, $course);

        $data = $this->validasi($request);

        $modul = DB::transaction(fn (): CourseModule => $course->modules()->create([
            ...$data,
            'number' => (int) $course->modules()->max('number') + 1,
        ]));

        return response()->json(self::bentuk($modul), 201);
    }

    public function update(Request $request, CourseModule $module): JsonResponse
    {
        $this->pastikanPengampu($request, $module->course);

        $module->update($this->validasi($request));

        return response()->json(self::bentuk($module));
    }

    public function destroy(Request $request, CourseModule $module): JsonResponse
    {
        $course = $module->course;
        $this->pastikanPengampu($request, $course);

        DB::transaction(function () use ($module, $course): void {
            $module->delete();

            // Nomor dirapatkan lagi supaya tidak ada "Modul 4" setelah
            // "Modul 2". Aman terhadap indeks unik (course_id, number): nomor
            // hanya turun ke celah yang sudah kosong.
            $course->modules()->get()->values()->each(function (CourseModule $m, int $i): void {
                if ($m->number !== $i + 1) {
                    $m->update(['number' => $i + 1]);
                }
            });
        });

        return response()->json(['deleted' => true]);
    }

    /** @return array<string, mixed> */
    public static function bentuk(CourseModule $m): array
    {
        return [
            'id' => $m->id,
            'number' => $m->number,
            'title' => $m->title,
            'description' => $m->description,
            'url' => $m->url,
        ];
    }

    /** @return array{title: string, description: ?string, url: ?string} */
    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            // Hanya http(s): tautan ini dibuka siswa, jadi skema seperti
            // javascript: tidak boleh lolos.
            'url' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'url' => $data['url'] ?? null,
        ];
    }

    /** 404, bukan 403: kursus guru lain tidak perlu terlihat ada. */
    private function pastikanPengampu(Request $request, ?Course $course): void
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        if ($course?->teacher_id !== $guru->id) {
            throw new NotFoundHttpException('Kursus tidak ditemukan.');
        }
    }
}
