<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Koleksi LKPD dan bahan ajar milik guru sendiri. Isinya ditautkan, bukan
 * diunggah — sama dengan modul kursus siswa, supaya berkas tetap di Drive
 * madrasah dan tidak menggandakan penyimpanan.
 */
class BahanAjarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $jenis = in_array($request->query('jenis'), array_keys(TeachingMaterial::JENIS), true)
            ? $request->query('jenis')
            : null;

        $daftar = TeachingMaterial::query()
            ->with('subject')
            ->where('teacher_id', $guru->id)
            ->when($jenis, fn ($q) => $q->where('type', $jenis))
            ->latest('id')
            ->limit(100)
            ->get();

        return response()->json([
            'selected' => $jenis,
            'types' => TeachingMaterial::JENIS,
            'counts' => TeachingMaterial::query()
                ->where('teacher_id', $guru->id)
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type'),
            'materials' => $daftar->map(fn (TeachingMaterial $m): array => [
                'id' => $m->id,
                'type' => $m->type,
                'type_label' => $m->labelJenis(),
                'title' => $m->title,
                'subject' => $m->subject?->name,
                'level' => $m->level,
                'url' => $m->url,
                'description' => $m->description,
            ]),
            'subjects' => Subject::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $bahan = TeachingMaterial::query()->create([
            ...$this->validasi($request),
            'teacher_id' => $guru->id,
        ]);

        return response()->json(['id' => $bahan->id], 201);
    }

    public function update(Request $request, TeachingMaterial $material): JsonResponse
    {
        $this->pastikanMilikSendiri($request, $material);

        $material->update($this->validasi($request));

        return response()->json(['id' => $material->id]);
    }

    public function destroy(Request $request, TeachingMaterial $material): JsonResponse
    {
        $this->pastikanMilikSendiri($request, $material);

        $material->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(TeachingMaterial::JENIS))],
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'level' => ['nullable', 'string', 'max:20'],
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        return [
            'type' => $data['type'],
            'title' => $data['title'],
            'subject_id' => $data['subject_id'] ?? null,
            'level' => $data['level'] ?? null,
            'url' => $data['url'] ?? null,
            'description' => $data['description'] ?? null,
        ];
    }

    private function pastikanMilikSendiri(Request $request, TeachingMaterial $material): void
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        if ($material->teacher_id !== $guru->id) {
            throw new NotFoundHttpException('Bahan ajar tidak ditemukan.');
        }
    }
}
