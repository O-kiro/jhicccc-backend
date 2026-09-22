<?php

namespace App\Support;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Collection;

/**
 * Pasangan mapel–kelas yang diampu seorang guru.
 *
 * Diambil dari jadwal dan dari kursus sekaligus: di lapangan keduanya tidak
 * selalu diisi bersamaan — ada guru yang punya jadwal tapi kursusnya belum
 * dibuat, dan sebaliknya.
 */
final class Pengampuan
{
    /**
     * Dikunci "subject_id-classroom_id", diurutkan per kelas lalu mapel.
     *
     * @return Collection<string, array{key: string, subject_id: int, classroom_id: int, subject: string, classroom: string}>
     */
    public static function untuk(Teacher $guru): Collection
    {
        $baris = $guru->schedules()->get(['subject_id', 'classroom_id'])
            ->concat($guru->courses()->get(['subject_id', 'classroom_id']))
            ->filter(fn ($r): bool => $r->subject_id && $r->classroom_id)
            ->unique(fn ($r): string => self::kunci($r->subject_id, $r->classroom_id));

        $mapel = Subject::query()->whereIn('id', $baris->pluck('subject_id'))->pluck('name', 'id');
        $kelas = Classroom::query()->whereIn('id', $baris->pluck('classroom_id'))->pluck('name', 'id');

        return $baris
            ->map(fn ($r): array => [
                'key' => self::kunci($r->subject_id, $r->classroom_id),
                'subject_id' => (int) $r->subject_id,
                'classroom_id' => (int) $r->classroom_id,
                'subject' => (string) ($mapel[$r->subject_id] ?? '—'),
                'classroom' => (string) ($kelas[$r->classroom_id] ?? '—'),
            ])
            ->sortBy([['classroom', 'asc'], ['subject', 'asc']])
            ->keyBy('key');
    }

    /** @return list<int> */
    public static function kelas(Teacher $guru): array
    {
        return self::untuk($guru)->pluck('classroom_id')->unique()->values()->all();
    }

    /**
     * Jumlah siswa aktif per kelas.
     *
     * @param  iterable<int>  $idKelas
     * @return Collection<int, int>
     */
    public static function ukuranKelas(iterable $idKelas): Collection
    {
        return Student::query()
            ->whereIn('classroom_id', collect($idKelas)->all())
            ->where('is_active', true)
            ->selectRaw('classroom_id, COUNT(*) as total')
            ->groupBy('classroom_id')
            ->pluck('total', 'classroom_id')
            ->map(fn ($n): int => (int) $n);
    }

    public static function kunci(int|string $idMapel, int|string $idKelas): string
    {
        return $idMapel.'-'.$idKelas;
    }
}
