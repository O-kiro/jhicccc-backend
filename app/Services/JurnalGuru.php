<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Jurnal mengajar dari sisi guru: sesi mana yang belum dicatat.
 *
 * "Tertunda" berarti sesi yang jamnya sudah mulai — hari ini maupun beberapa
 * hari ke belakang — tapi belum punya jurnal. Sesi yang belum dimulai tidak
 * dihitung; menagihnya hanya membuat angka di dasbor terlihat buruk.
 */
class JurnalGuru
{
    /** Seberapa jauh ke belakang sesi kosong masih ditagih. */
    public const RENTANG_HARI = 7;

    /** Batas mundur pengisian; lebih dari ini harus lewat tata usaha. */
    public const BATAS_MUNDUR_HARI = 30;

    /**
     * @return Collection<int, array{schedule: Schedule, date: CarbonImmutable}>
     */
    public function tertunda(Teacher $guru): Collection
    {
        $jadwal = $guru->schedules()->with(['subject', 'classroom'])->orderBy('starts_at')->get();

        if ($jadwal->isEmpty()) {
            return collect();
        }

        $hariIni = CarbonImmutable::today();
        $mulai = $hariIni->subDays(self::RENTANG_HARI - 1);

        $terisi = TeachingJournal::query()
            ->whereIn('schedule_id', $jadwal->modelKeys())
            ->whereDate('date', '>=', $mulai->toDateString())
            ->get(['schedule_id', 'date'])
            ->map(fn (TeachingJournal $j): string => $j->schedule_id.'|'.$j->date->toDateString())
            ->flip();

        $perHari = $jadwal->groupBy('day_of_week');
        $jam = now()->format('H:i:s');
        $hasil = collect();

        for ($tanggal = $hariIni; $tanggal->gte($mulai); $tanggal = $tanggal->subDay()) {
            foreach ($perHari->get($tanggal->dayOfWeekIso, []) as $sesi) {
                if ($tanggal->isSameDay($hariIni) && $jam < $sesi->starts_at) {
                    continue;
                }

                if ($terisi->has($sesi->id.'|'.$tanggal->toDateString())) {
                    continue;
                }

                $hasil->push(['schedule' => $sesi, 'date' => $tanggal]);
            }
        }

        return $hasil;
    }
}
