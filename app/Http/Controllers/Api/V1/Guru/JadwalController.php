<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Guru\SesiResource;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Support\Hari;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /** Jadwal mengajar sepekan, dikelompokkan per hari. */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $jadwal = $guru->schedules()
            ->with(['subject', 'classroom'])
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->get();

        // Senin–Sabtu selalu tampil supaya hari kosong terlihat kosong;
        // Minggu hanya bila memang ada jadwalnya.
        $hari = collect(Hari::NAMA)
            ->filter(fn (string $_, int $d): bool => $d <= 6 || $jadwal->contains('day_of_week', $d));

        $menit = $jadwal->sum(fn (Schedule $s): int => (int) Carbon::parse($s->starts_at)
            ->diffInMinutes(Carbon::parse($s->ends_at)));

        return response()->json([
            'days' => $hari->map(fn (string $label, int $d): array => [
                'day' => $d,
                'label' => $label,
                'is_today' => $d === (int) now()->dayOfWeekIso,
                'sessions' => SesiResource::collection($jadwal->where('day_of_week', $d)->values()),
            ])->values(),
            'summary' => [
                'sessions' => $jadwal->count(),
                'minutes' => $menit,
                'classes' => $jadwal->pluck('classroom_id')->unique()->count(),
                'subjects' => $jadwal->pluck('subject.name')->unique()->values(),
            ],
        ]);
    }
}
