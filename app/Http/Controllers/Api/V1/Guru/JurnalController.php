<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Services\JurnalGuru;
use App\Support\Hari;
use App\Support\Pengampuan;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Jurnal mengajar yang diisi guru sendiri. Hasilnya tercatat di tabel yang
 * sama dengan modul Jurnal KBM di panel admin, jadi keterisiannya langsung
 * terpantau Wakasek Kurikulum.
 *
 * Kepemilikan dinilai dari jadwalnya, bukan dari siapa pencatatnya: jurnal
 * milik jadwal guru ini — termasuk yang dicatat admin — boleh ia ubah.
 */
class JurnalController extends Controller
{
    public function index(Request $request, JurnalGuru $jurnal): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $jadwal = $guru->schedules()
            ->with(['subject', 'classroom'])
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->get();

        $ukuran = Pengampuan::ukuranKelas($jadwal->pluck('classroom_id')->unique());

        $sesi = fn (Schedule $s): array => [
            'schedule_id' => $s->id,
            'subject' => $s->subject->name,
            'classroom' => $s->classroom->name,
            'start' => substr((string) $s->starts_at, 0, 5),
            'end' => substr((string) $s->ends_at, 0, 5),
            'class_size' => $ukuran[$s->classroom_id] ?? 0,
        ];

        $riwayat = TeachingJournal::query()
            ->whereIn('schedule_id', $jadwal->modelKeys())
            ->latest('date')
            ->latest('id')
            ->limit(30)
            ->get();

        return response()->json([
            'pending' => $jurnal->tertunda($guru)->map(fn (array $t): array => [
                ...$sesi($t['schedule']),
                'date' => $t['date']->toDateString(),
            ])->values(),
            'journals' => $riwayat->map(fn (TeachingJournal $j): array => [
                ...$sesi($jadwal->find($j->schedule_id)),
                'id' => $j->id,
                'date' => $j->date->toDateString(),
                'topic' => $j->topic,
                'note' => $j->note,
                'present_count' => $j->present_count,
            ]),
            // Untuk mengisi jurnal di luar daftar tertunda, misalnya kelas
            // pengganti atau hari yang sudah lewat dari rentang tagihan.
            'schedules' => $jadwal->map(fn (Schedule $s): array => [
                ...$sesi($s),
                'day' => $s->day_of_week,
                'day_label' => Hari::nama($s->day_of_week),
            ]),
            // Tanggal hari ini menurut jam madrasah, untuk batas isian tanggal
            // di formulir — zona waktu server Next.js belum tentu WIB.
            'today' => today()->toDateString(),
            'range_days' => JurnalGuru::RENTANG_HARI,
            'max_back_days' => JurnalGuru::BATAS_MUNDUR_HARI,
        ]);
    }

    /** Mencatat jurnal, atau memperbaruinya bila sesi itu sudah punya jurnal. */
    public function store(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        $data = $request->validate([
            'schedule_id' => ['required', 'integer', Rule::exists('schedules', 'id')->where('teacher_id', $guru->id)],
            'date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
                'after_or_equal:'.today()->subDays(JurnalGuru::BATAS_MUNDUR_HARI)->toDateString(),
            ],
            'topic' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'present_count' => ['nullable', 'integer', 'min:0'],
        ], [
            'date.before_or_equal' => 'Jurnal tidak bisa diisi untuk tanggal yang belum tiba.',
            'date.after_or_equal' => 'Paling jauh '.JurnalGuru::BATAS_MUNDUR_HARI.' hari ke belakang; selebihnya hubungi tata usaha.',
        ]);

        $sesi = Schedule::query()->findOrFail($data['schedule_id']);
        $tanggal = CarbonImmutable::parse($data['date']);

        if ($tanggal->dayOfWeekIso !== $sesi->day_of_week) {
            throw ValidationException::withMessages([
                'date' => 'Jadwal ini hari '.Hari::nama($sesi->day_of_week)
                    .', sedangkan tanggal itu hari '.Hari::nama($tanggal->dayOfWeekIso).'.',
            ]);
        }

        $ukuran = Pengampuan::ukuranKelas([$sesi->classroom_id])[$sesi->classroom_id] ?? 0;

        if (($data['present_count'] ?? null) !== null && $data['present_count'] > $ukuran) {
            throw ValidationException::withMessages([
                'present_count' => "Jumlah hadir melebihi jumlah siswa kelas ini ({$ukuran}).",
            ]);
        }

        $isi = [
            'teacher_id' => $guru->id,
            'topic' => $data['topic'],
            'note' => $data['note'] ?? null,
            'present_count' => $data['present_count'] ?? null,
        ];

        // Dicari dengan whereDate: kolom tanggal tersimpan "Y-m-d 00:00:00",
        // jadi updateOrCreate dengan "Y-m-d" tidak pernah menemukan barisnya
        // dan malah menabrak indeks unik.
        $ada = TeachingJournal::query()
            ->where('schedule_id', $sesi->id)
            ->whereDate('date', $tanggal->toDateString())
            ->first();

        if ($ada) {
            $ada->update($isi);

            return response()->json(['id' => $ada->id, 'updated' => true]);
        }

        $baru = TeachingJournal::query()->create([
            ...$isi,
            'schedule_id' => $sesi->id,
            'date' => $tanggal->toDateString(),
        ]);

        return response()->json(['id' => $baru->id, 'updated' => false], 201);
    }

    public function destroy(Request $request, TeachingJournal $journal): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        // 404, bukan 403: jurnal guru lain tidak perlu terlihat ada.
        if ($journal->schedule?->teacher_id !== $guru->id) {
            throw new NotFoundHttpException('Jurnal tidak ditemukan.');
        }

        $journal->delete();

        return response()->json(['deleted' => true]);
    }
}
