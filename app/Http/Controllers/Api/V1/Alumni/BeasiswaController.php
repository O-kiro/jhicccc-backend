<?php

namespace App\Http\Controllers\Api\V1\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeasiswaController extends Controller
{
    /**
     * Katalog beasiswa beserta ringkasannya.
     *
     * Ringkasan dihitung dari seluruh katalog, bukan dari hasil yang sedang
     * disaring — angka "total kuota" tidak boleh ikut berubah saat pengguna
     * memilih kategori.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $kategori = trim((string) $request->query('kategori'));

        $semua = Scholarship::query()->orderBy('sort')->orderBy('id')->get();
        $aktif = $semua->whereIn('status', ['dibuka', 'segera_ditutup']);

        $kuota = (int) $aktif->sum('quota');
        $terverifikasi = (int) $semua->sum('verified');

        $tampil = $kategori === ''
            ? $semua
            : $semua->where('category', $kategori);

        return response()->json([
            'summary' => [
                'program_aktif' => $aktif->count(),
                'program_total' => $semua->count(),
                'kuota' => $kuota,
                'pendaftar' => (int) $semua->sum('applicants'),
                'terverifikasi' => $terverifikasi,
                // Penyerapan kursi: berapa kuota yang sudah terisi pendaftar
                // yang lolos verifikasi.
                'penyerapan' => $kuota > 0 ? (int) round($terverifikasi / $kuota * 100) : 0,
                'sisa_kuota' => max(0, $kuota - $terverifikasi),
            ],
            'categories' => $semua->pluck('category')->unique()->sort()->values(),
            'selected' => $kategori !== '' ? $kategori : null,
            'scholarships' => $tampil->values()->map(fn (Scholarship $b): array => [
                'id' => $b->id,
                'category' => $b->category,
                'name' => $b->name,
                'quota' => $b->quota,
                'benefits' => $b->benefits,
                'target' => $b->target,
                'deadline' => $b->deadline?->toDateString(),
                'status' => $b->status,
                'status_label' => $b->labelStatus(),
                'url' => $b->url,
            ]),
        ]);
    }
}
