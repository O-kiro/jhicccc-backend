<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Models\DisciplineRecord;
use App\Models\DisciplineRule;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\Pengampuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Lapor poin kedisiplinan. Laporannya masuk ke tabel yang sama dengan modul
 * Kesiswaan di panel admin, jadi Wakasek langsung melihatnya.
 */
class TatibController extends Controller
{
    /** Seberapa jauh ke belakang kejadian masih boleh dilaporkan. */
    private const BATAS_MUNDUR_HARI = 60;

    public function index(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $idKelas = Pengampuan::kelas($guru);

        $riwayat = DisciplineRecord::query()
            ->with(['student.classroom', 'rule'])
            ->where('teacher_id', $guru->id)
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json([
            'students' => Student::query()
                ->whereIn('classroom_id', $idKelas)
                ->where('is_active', true)
                ->with('classroom:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'nisn', 'classroom_id'])
                ->map(fn (Student $s): array => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'nisn' => $s->nisn,
                    'classroom_id' => $s->classroom_id,
                    'classroom' => $s->classroom?->name,
                ]),
            'classrooms' => Pengampuan::untuk($guru)
                ->values()
                ->map(fn (array $k): array => ['id' => $k['classroom_id'], 'name' => $k['classroom']])
                ->unique('id')
                ->values(),
            'rules' => DisciplineRule::query()
                ->where('is_active', true)
                ->orderBy('kind')
                ->orderBy('title')
                ->get(['id', 'code', 'title', 'kind', 'points', 'category']),
            'reports' => $riwayat->map(fn (DisciplineRecord $r): array => [
                'id' => $r->id,
                'student' => $r->student->name,
                'classroom' => $r->student->classroom?->name,
                'rule' => $r->rule->title,
                'kind' => $r->rule->kind,
                'points' => $r->points,
                'occurred_on' => $r->occurred_on->toDateString(),
                'note' => $r->note,
            ]),
            'today' => today()->toDateString(),
            'earliest' => today()->subDays(self::BATAS_MUNDUR_HARI)->toDateString(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $idSiswa = Student::query()
            ->whereIn('classroom_id', Pengampuan::kelas($guru))
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        $data = $request->validate([
            'student_id' => ['required', 'integer', Rule::in($idSiswa)],
            'discipline_rule_id' => ['required', 'integer', Rule::exists('discipline_rules', 'id')->where('is_active', true)],
            'occurred_on' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
                'after_or_equal:'.today()->subDays(self::BATAS_MUNDUR_HARI)->toDateString(),
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'student_id.in' => 'Siswa ini bukan siswa di kelas yang Anda ampu.',
            'occurred_on.before_or_equal' => 'Tanggal kejadian tidak boleh di masa depan.',
            'occurred_on.after_or_equal' => 'Paling jauh '.self::BATAS_MUNDUR_HARI.' hari ke belakang.',
        ]);

        $aturan = DisciplineRule::query()->findOrFail($data['discipline_rule_id']);

        // Poin disalin dari aturan saat dilaporkan: mengubah bobot aturan
        // kelak tidak boleh menulis ulang riwayat.
        $laporan = DisciplineRecord::query()->create([
            'student_id' => $data['student_id'],
            'discipline_rule_id' => $aturan->id,
            'teacher_id' => $guru->id,
            'occurred_on' => $data['occurred_on'],
            'points' => $aturan->points,
            'note' => $data['note'] ?? null,
        ]);

        return response()->json(['id' => $laporan->id, 'points' => $laporan->points], 201);
    }
}
