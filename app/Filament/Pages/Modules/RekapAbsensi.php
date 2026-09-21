<?php

namespace App\Filament\Pages\Modules;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Student;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * Matriks kehadiran sebulan dalam format buku induk: siswa dikali tanggal.
 *
 * Disusun di sini, bukan lewat tabel Filament, karena kolomnya berubah-ubah
 * mengikuti jumlah hari pada bulan yang dipilih.
 */
class RekapAbsensi extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|UnitEnum|null $navigationGroup = 'Absensi';

    protected static ?string $navigationLabel = 'Rekap Bulanan';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Rekap Absensi Bulanan';

    protected string $view = 'filament.pages.modules.rekap-absensi';

    public string $bulan;

    public ?string $kelas = null;

    public function mount(): void
    {
        $this->bulan = now()->format('Y-m');
    }

    /** @return array<string, string> */
    public function getKelasOptions(): array
    {
        return Classroom::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    public function getPeriode(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $this->bulan.'-01')->startOfMonth();
    }

    /** @return array<int, int> */
    public function getTanggalList(): array
    {
        $awal = $this->getPeriode();

        return range(1, $awal->daysInMonth);
    }

    /**
     * Satu baris per siswa: identitas, kode per tanggal, dan rekapitulasinya.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getBaris(): Collection
    {
        $awal = $this->getPeriode();
        $akhir = $awal->endOfMonth();

        $siswa = Student::query()
            ->where('is_active', true)
            ->when($this->kelas, fn ($q) => $q->where('classroom_id', $this->kelas))
            ->with('classroom')
            ->orderBy('name')
            ->get();

        // Satu kueri untuk sebulan penuh, lalu dikelompokkan di memori —
        // bukan satu kueri per siswa per tanggal.
        $kehadiran = Attendance::query()
            ->whereIn('student_id', $siswa->pluck('id'))
            ->whereBetween('date', [$awal->toDateString(), $akhir->toDateString()])
            ->get()
            ->groupBy('student_id');

        return $siswa->map(function (Student $s) use ($kehadiran): array {
            $milik = ($kehadiran[$s->id] ?? collect())
                ->keyBy(fn (Attendance $a): int => (int) $a->date->format('j'));

            return [
                'siswa' => $s,
                'kode' => $milik->map(fn (Attendance $a): string => $a->code)->all(),
                'rekap' => collect(array_keys(Attendance::KODE))
                    ->mapWithKeys(fn (string $k): array => [
                        $k => $milik->where('code', $k)->count(),
                    ])
                    ->all(),
            ];
        });
    }
}
