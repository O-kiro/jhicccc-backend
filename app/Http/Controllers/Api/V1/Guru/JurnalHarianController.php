<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\TeacherActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Jurnal harian: kegiatan guru di luar jam mengajar kelas. Berbeda dari
 * Jurnal Mengajar yang terikat satu jadwal pelajaran.
 */
class JurnalHarianController extends Controller
{
    /** Batas ukuran bukti foto, dalam kilobita. */
    private const MAKS_FOTO_KB = 3072;

    public function index(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $filter = $request->validate([
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $awalTahun = now()->startOfYear()->toDateString();

        $daftar = TeacherActivity::query()
            ->with('classroom')
            ->where('teacher_id', $guru->id)
            ->when($filter['dari'] ?? null, fn ($q, $d) => $q->whereDate('date', '>=', $d))
            ->when($filter['sampai'] ?? null, fn ($q, $d) => $q->whereDate('date', '<=', $d))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return response()->json([
            'filters' => ['dari' => $filter['dari'] ?? null, 'sampai' => $filter['sampai'] ?? null],
            'counts' => [
                'tahun_ini' => TeacherActivity::query()
                    ->where('teacher_id', $guru->id)
                    ->whereDate('date', '>=', $awalTahun)
                    ->count(),
                'arsip' => TeacherActivity::query()
                    ->where('teacher_id', $guru->id)
                    ->whereDate('date', '<', $awalTahun)
                    ->count(),
            ],
            'activities' => $daftar->map(fn (TeacherActivity $a): array => [
                'id' => $a->id,
                'date' => $a->date->toDateString(),
                'classroom' => $a->classroom?->name,
                'activity' => $a->activity,
                'photo_url' => $a->photoUrl(),
            ]),
            'classrooms' => Classroom::query()->orderBy('name')->get(['id', 'name']),
            'today' => today()->toDateString(),
            'max_photo_kb' => self::MAKS_FOTO_KB,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'classroom_id' => ['nullable', 'integer', Rule::exists('classrooms', 'id')],
            'activity' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:'.self::MAKS_FOTO_KB],
        ], [
            'date.before_or_equal' => 'Kegiatan tidak bisa dicatat untuk tanggal yang belum tiba.',
        ]);

        $kegiatan = TeacherActivity::query()->create([
            'teacher_id' => $guru->id,
            'classroom_id' => $data['classroom_id'] ?? null,
            'date' => $data['date'],
            'activity' => $data['activity'],
            'photo_path' => $request->file('photo')?->store('guru/jurnal-harian', 'public'),
        ]);

        return response()->json(['id' => $kegiatan->id, 'photo_url' => $kegiatan->photoUrl()], 201);
    }

    public function destroy(Request $request, TeacherActivity $activity): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        // 404, bukan 403: jurnal guru lain tidak perlu terlihat ada.
        if ($activity->teacher_id !== $guru->id) {
            throw new NotFoundHttpException('Kegiatan tidak ditemukan.');
        }

        // Foto ikut terhapus lewat MembersihkanGambarUnggahan.
        $activity->delete();

        return response()->json(['deleted' => true]);
    }
}
