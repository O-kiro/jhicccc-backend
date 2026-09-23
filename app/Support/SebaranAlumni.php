<?php

namespace App\Support;

use App\Models\AlumniOutcome;

/**
 * Rekap sebaran kelulusan alumni.
 *
 * Persentase selalu dihitung di sini, tidak pernah disimpan: kalau angkanya
 * dikoreksi admin, persentasenya ikut benar tanpa perlu diperbarui manual.
 */
final class SebaranAlumni
{
    /** Kategori yang dihitung sebagai melanjutkan ke jalur negeri. */
    private const NEGERI = ['ptn', 'kedinasan'];

    /**
     * @return array<string, mixed>
     */
    public static function untukTahun(?int $tahun): array
    {
        $tahunTersedia = AlumniOutcome::query()
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->all();

        $dipilih = in_array($tahun, $tahunTersedia, true) ? $tahun : ($tahunTersedia[0] ?? null);

        $baris = $dipilih === null
            ? collect()
            : AlumniOutcome::query()
                ->where('year', $dipilih)
                ->orderBy('sort')
                ->orderByDesc('students')
                ->get();

        $total = (int) $baris->sum('students');

        return [
            'years' => $tahunTersedia,
            'year' => $dipilih,
            'total' => $total,
            'outcomes' => $baris->map(fn (AlumniOutcome $o): array => [
                'category' => $o->category,
                'label' => $o->label(),
                'tone' => $o->tone(),
                'students' => $o->students,
                'percent' => $total > 0 ? round($o->students / $total * 100, 1) : 0.0,
                'note' => $o->note,
            ])->values(),
        ];
    }

    /**
     * Ringkasan tahun terbaru untuk beranda.
     *
     * @return array{year: ?int, total: int, lanjut_studi_negeri: int, lanjut_studi_negeri_persen: float}
     */
    public static function tahunTerbaru(): array
    {
        $data = self::untukTahun(null);
        $total = $data['total'];

        $negeri = (int) collect($data['outcomes'])
            ->whereIn('category', self::NEGERI)
            ->sum('students');

        return [
            'year' => $data['year'],
            'total' => $total,
            'lanjut_studi_negeri' => $negeri,
            'lanjut_studi_negeri_persen' => $total > 0 ? round($negeri / $total * 100, 1) : 0.0,
        ];
    }
}
