<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AnnouncementResource;
use App\Http\Resources\V1\Guru\SesiResource;
use App\Http\Resources\V1\TeacherResource;
use App\Models\Announcement;
use App\Models\Quote;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Services\JurnalGuru;
use App\Support\Pengampuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    /**
     * Beranda Portal Guru: ringkasan, jadwal mengajar hari ini, dan
     * pengumuman dalam satu permintaan — dipakai juga oleh sidebar.
     */
    public function __invoke(Request $request, JurnalGuru $jurnal): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();
        $guru->load('homeroomClassrooms');

        $hariIni = $guru->schedules()
            ->forToday()
            ->with(['subject', 'classroom'])
            ->orderBy('starts_at')
            ->get();

        $terisi = TeachingJournal::query()
            ->whereIn('schedule_id', $hariIni->modelKeys())
            ->whereDate('date', today()->toDateString())
            ->pluck('schedule_id')
            ->all();

        $kelas = Pengampuan::kelas($guru);
        $jam = now()->format('H:i:s');
        $quote = Quote::hariIni();

        return response()->json([
            'teacher' => new TeacherResource($guru),
            'quote' => $quote ? ['body' => $quote->body, 'source' => $quote->source] : null,
            'summary' => [
                'classes' => count($kelas),
                'students' => Pengampuan::ukuranKelas($kelas)->sum(),
                'sessions_today' => $hariIni->count(),
                'journals_pending' => $jurnal->tertunda($guru)->count(),
                'active_loans' => $guru->bookLoans()->active()->count(),
            ],
            'today_schedule' => $hariIni->map(fn (Schedule $s): array => [
                ...(new SesiResource($s))->resolve($request),
                'journal_filled' => in_array($s->id, $terisi, true),
                // Dihitung di sini, bukan di browser: zona waktu server Next.js
                // belum tentu WIB.
                'started' => $jam >= $s->starts_at,
            ]),
            'announcements' => AnnouncementResource::collection(
                Announcement::query()->published()->latest('published_at')->limit(3)->get(),
            ),
        ]);
    }
}
