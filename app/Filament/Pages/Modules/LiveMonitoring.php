<?php

namespace App\Filament\Pages\Modules;

use App\Filament\Concerns\DibatasiPeran;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\StudentPermit;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * Ringkasan kehadiran hari berjalan.
 *
 * Semuanya dihitung dari attendances dan student_permits saat halaman
 * dibuka — tidak ada angka yang disimpan terpisah, jadi koreksi kehadiran
 * langsung terlihat di sini.
 */
class LiveMonitoring extends Page
{
    use DibatasiPeran;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static string|UnitEnum|null $navigationGroup = 'Absensi';

    protected static ?string $navigationLabel = 'Live Monitoring';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Live Monitoring Kehadiran';

    protected string $view = 'filament.pages.modules.live-monitoring';

    /** @return array<string, int> */
    public function getRingkasan(): array
    {
        $hariIni = Attendance::query()
            ->whereDate('date', now()->toDateString())
            ->get();

        $ringkasan = ['Total Siswa' => Student::query()->where('is_active', true)->count()];

        foreach (Attendance::KODE as $kode => $arti) {
            $ringkasan[$arti] = $hariIni->where('code', $kode)->count();
        }

        // Yang belum tercatat sama sekali hari ini — bukan alpha, memang
        // belum ada datanya.
        $ringkasan['Belum Tercatat'] = max(0, $ringkasan['Total Siswa'] - $hariIni->count());

        return $ringkasan;
    }

    /** @return array<string, int> */
    public function getSumber(): array
    {
        $hariIni = Attendance::query()
            ->whereDate('date', now()->toDateString())
            ->get();

        $keluaran = [];

        foreach (Attendance::SUMBER as $kunci => $label) {
            $keluaran[$label] = $hariIni->where('source', $kunci)->count();
        }

        return $keluaran;
    }

    /** @return Collection<int, StudentPermit> */
    public function getDiLuar()
    {
        return StudentPermit::query()
            ->outstanding()
            ->with(['student.classroom'])
            ->orderBy('left_at')
            ->get();
    }
}
