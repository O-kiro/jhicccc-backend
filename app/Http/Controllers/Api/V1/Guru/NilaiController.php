<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\Pengampuan;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Input nilai per kelas. Nilai yang disimpan di sini langsung muncul di
 * Rapor Digital siswa — grafik "Sejarah Nilai" dan "Penilaian Terbaru".
 *
 * Satu "penilaian" = satu judul pada satu tanggal untuk satu mapel. Mengirim
 * judul dan tanggal yang sama lagi berarti menyunting nilainya, bukan
 * menggandakannya.
 */
class NilaiController extends Controller
{
    /** Berapa penilaian terakhir yang ditampilkan per kelas. */
    private const RIWAYAT = 12;

    public function index(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $kelas = Pengampuan::untuk($guru);
        $terpilih = $kelas->get((string) $request->query('kelas')) ?? $kelas->first();

        return response()->json([
            'classes' => $kelas->values()->map(fn (array $k): array => [
                'key' => $k['key'],
                'subject' => $k['subject'],
                'classroom' => $k['classroom'],
            ]),
            'selected' => $terpilih ? $this->rincian($terpilih) : null,
            // Batas isian tanggal di formulir, menurut jam madrasah.
            'today' => today()->toDateString(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $kelas = Pengampuan::untuk($guru);

        $data = $request->validate([
            'kelas' => ['required', 'string', Rule::in($kelas->keys()->all())],
            'title' => ['required', 'string', 'max:100'],
            'assessed_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'scores' => ['required', 'array', 'max:200'],
            'scores.*.student_id' => ['required', 'integer', 'distinct'],
            // Kosong berarti "belum dinilai" — atau menghapus nilai yang ada.
            'scores.*.score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], [
            'kelas.in' => 'Kelas ini bukan kelas yang Anda ampu.',
            'assessed_on.before_or_equal' => 'Tanggal penilaian tidak boleh di masa depan.',
        ]);

        $k = $kelas[$data['kelas']];
        $anggota = $this->siswa($k)->modelKeys();

        if (collect($data['scores'])->pluck('student_id')->diff($anggota)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'scores' => 'Ada siswa yang bukan anggota kelas '.$k['classroom'].'.',
            ]);
        }

        $tanggal = CarbonImmutable::parse($data['assessed_on'])->toDateString();
        $hasil = ['saved' => 0, 'removed' => 0];

        DB::transaction(function () use ($data, $k, $tanggal, &$hasil): void {
            // Satu kueri untuk semua nilai lama; whereDate karena tanggalnya
            // tersimpan "Y-m-d 00:00:00".
            $lama = Assessment::query()
                ->where('subject_id', $k['subject_id'])
                ->where('title', $data['title'])
                ->whereDate('assessed_on', $tanggal)
                ->whereIn('student_id', collect($data['scores'])->pluck('student_id'))
                ->get()
                ->keyBy('student_id');

            foreach ($data['scores'] as $baris) {
                $ada = $lama->get($baris['student_id']);
                $nilai = $baris['score'] ?? null;

                if ($nilai === null) {
                    if ($ada) {
                        $ada->delete();
                        $hasil['removed']++;
                    }

                    continue;
                }

                if ($ada) {
                    $ada->update(['score' => $nilai]);
                } else {
                    Assessment::query()->create([
                        'student_id' => $baris['student_id'],
                        'subject_id' => $k['subject_id'],
                        'title' => $data['title'],
                        'score' => $nilai,
                        'assessed_on' => $tanggal,
                    ]);
                }

                $hasil['saved']++;
            }
        });

        return response()->json($hasil);
    }

    /**
     * @param  array{key: string, subject_id: int, classroom_id: int, subject: string, classroom: string}  $k
     * @return array<string, mixed>
     */
    private function rincian(array $k): array
    {
        $siswa = $this->siswa($k);

        $penilaian = Assessment::query()
            ->where('subject_id', $k['subject_id'])
            ->whereIn('student_id', $siswa->modelKeys())
            ->orderByDesc('assessed_on')
            ->orderBy('title')
            ->get()
            ->groupBy(fn (Assessment $a): string => $a->assessed_on->toDateString().'|'.$a->title)
            ->take(self::RIWAYAT);

        return [
            'key' => $k['key'],
            'subject' => $k['subject'],
            'classroom' => $k['classroom'],
            'students' => $siswa->map(fn (Student $s): array => [
                'id' => $s->id,
                'name' => $s->name,
                'nisn' => $s->nisn,
            ]),
            'assessments' => $penilaian->map(fn (Collection $g): array => [
                'title' => (string) $g->first()->title,
                'assessed_on' => $g->first()->assessed_on->toDateString(),
                'count' => $g->count(),
                'average' => round((float) $g->avg('score'), 1),
                // Dikunci ID siswa, supaya formulir bisa langsung diisi ulang.
                'scores' => $g->mapWithKeys(fn (Assessment $a): array => [$a->student_id => $a->score]),
            ])->values(),
        ];
    }

    /**
     * @param  array{classroom_id: int}  $k
     * @return EloquentCollection<int, Student>
     */
    private function siswa(array $k): EloquentCollection
    {
        return Student::query()
            ->where('classroom_id', $k['classroom_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'nisn']);
    }
}
