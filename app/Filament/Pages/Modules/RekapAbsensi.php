<?php

namespace App\Filament\Pages\Modules;

use App\Filament\Concerns\DibatasiPeran;
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
    use DibatasiPeran;

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

    public function bulanSebelumnya(): void
    {
        $this->bulan = $this->getPeriode()->subMonth()->format('Y-m');
    }

    public function bulanBerikutnya(): void
    {
        $this->bulan = $this->getPeriode()->addMonth()->format('Y-m');
    }

    /** @return array<int, string> Inisial hari per tanggal: S, S, R, K, J, S, M. */
    public function getHariList(): array
    {
        $awal = $this->getPeriode();
        $inisial = [0 => 'M', 1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S'];

        return collect($this->getTanggalList())
            ->mapWithKeys(fn (int $t): array => [$t => $inisial[$awal->day($t)->dayOfWeek]])
            ->all();
    }

    /** @return list<int> Tanggal Sabtu dan Minggu. */
    public function getAkhirPekan(): array
    {
        $awal = $this->getPeriode();

        return array_values(array_filter($this->getTanggalList(), fn (int $t): bool => $awal->day($t)->isWeekend()));
    }

    /**
     * Persentase hadir: (Hadir + Terlambat) dibagi hari yang tercatat,
     * tanpa Libur. Null bila belum ada catatan sama sekali.
     *
     * @param  array<string, int>  $rekap
     */
    public static function persenHadir(array $rekap): ?float
    {
        $tercatat = array_sum($rekap) - ($rekap['L'] ?? 0);

        return $tercatat > 0 ? round((($rekap['H'] ?? 0) + ($rekap['T'] ?? 0)) / $tercatat * 100, 1) : null;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $baris
     * @return array{rekap: array<string, int>, persen: ?float, siswa: int}
     */
    public function getRingkasan(Collection $baris): array
    {
        $rekap = collect(array_keys(Attendance::KODE))
            ->mapWithKeys(fn (string $k): array => [$k => (int) $baris->sum(fn (array $r): int => $r['rekap'][$k])])
            ->all();

        return ['rekap' => $rekap, 'persen' => self::persenHadir($rekap), 'siswa' => $baris->count()];
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
