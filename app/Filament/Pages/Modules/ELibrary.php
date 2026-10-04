<?php

namespace App\Filament\Pages\Modules;

use App\Filament\Concerns\DibatasiPeran;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\Sirkulasi;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use UnitEnum;

/**
 * Meja sirkulasi: tap kartu siswa (NISN) atau guru (NIP), pindai atau pilih
 * buku, lalu proses.
 *
 * Kolom NISN dan kode buku sengaja kolom teks biasa: pembaca RFID maupun
 * barcode "mengetik" kodenya ke kolom yang sedang aktif lalu menekan Enter,
 * jadi alat semacam itu langsung bekerja tanpa integrasi khusus.
 */
class ELibrary extends Page
{
    use DibatasiPeran;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static string|UnitEnum|null $navigationGroup = 'E-Library';

    protected static ?string $navigationLabel = 'Sirkulasi';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Meja Sirkulasi';

    protected string $view = 'filament.pages.modules.sirkulasi';

    /** Nomor yang dipindai — NISN siswa atau NIP guru. */
    public string $nisn = '';

    /**
     * 'siswa' atau 'guru'; bersama $peminjamId menunjuk peminjam yang
     * dilayani. Dikunci supaya tidak bisa diganti dari browser — hanya
     * cariSiswa() yang boleh mengisinya.
     */
    #[Locked]
    public ?string $jenis = null;

    #[Locked]
    public ?int $peminjamId = null;

    public string $buku = '';

    public int $durasi = 14;

    /** Membaca kartu siswa atau guru. */
    public function cariSiswa(Sirkulasi $sirkulasi): void
    {
        $this->resetErrorBag();

        try {
            $orang = $sirkulasi->peminjam($this->nisn);
            $this->jenis = $orang instanceof Teacher ? 'guru' : 'siswa';
            $this->peminjamId = $orang->id;
        } catch (ValidationException $e) {
            $this->reset(['jenis', 'peminjamId']);
            $this->setErrorBag($e->validator->errors());
        }
    }

    public function pinjam(Sirkulasi $sirkulasi): void
    {
        $this->resetErrorBag();
        $peminjam = $this->getPeminjam();

        if (! $peminjam) {
            $this->addError('nisn', 'Tap kartu siswa atau guru lebih dulu.');

            return;
        }

        try {
            $pinjaman = $sirkulasi->pinjam($peminjam, $sirkulasi->buku($this->buku), $this->durasi);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());

            return;
        }

        Notification::make()
            ->title('Peminjaman tercatat')
            ->body("{$pinjaman->book->title} — kembali {$pinjaman->due_on->translatedFormat('j F Y')}.")
            ->success()
            ->send();

        $this->buku = '';
    }

    public function kembalikan(int $pinjamanId, Sirkulasi $sirkulasi): void
    {
        $peminjam = $this->getPeminjam();

        // Hanya pinjaman milik peminjam yang sedang dilayani.
        $pinjaman = $peminjam?->bookLoans()->whereKey($pinjamanId)->first();

        if (! $pinjaman) {
            return;
        }

        try {
            $sirkulasi->kembalikan($pinjaman);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());

            return;
        }

        Notification::make()->title('Buku dikembalikan')->body($pinjaman->book->title)->success()->send();
    }

    /** Peminjam berikutnya: kosongkan meja. */
    public function selesai(): void
    {
        $this->reset(['nisn', 'jenis', 'peminjamId', 'buku']);
        $this->resetErrorBag();
    }

    public function getPeminjam(): Student|Teacher|null
    {
        return match ($this->jenis) {
            'siswa' => Student::query()->with('classroom')->find($this->peminjamId),
            'guru' => Teacher::query()->find($this->peminjamId),
            default => null,
        };
    }

    /** @return Collection<int, BookLoan> */
    public function getPinjamanAktif(): Collection
    {
        return $this->getPeminjam()?->bookLoans()->active()->with('book')->orderBy('due_on')->get()
            ?? new Collection;
    }

    /** @return array<int, string> */
    public function getBukuOptions(): array
    {
        return Book::query()
            ->orderBy('title')
            ->get()
            ->mapWithKeys(fn (Book $b): array => [
                $b->id => ($b->code ? "[{$b->code}] " : '').$b->title,
            ])
            ->all();
    }

    /** @return array<int, string> */
    public function getDurasiOptions(): array
    {
        return Sirkulasi::DURASI;
    }

    /**
     * Ringkasan meja hari ini — supaya halaman tetap berguna sebelum ada
     * kartu yang ditap.
     *
     * @return array{aktif: int, pinjam_hari_ini: int, kembali_hari_ini: int, terlambat: int}
     */
    public function getRingkasan(): array
    {
        return [
            'aktif' => BookLoan::query()->active()->count(),
            'pinjam_hari_ini' => BookLoan::query()->whereDate('created_at', today())->count(),
            'kembali_hari_ini' => BookLoan::query()->whereDate('returned_at', today())->count(),
            'terlambat' => BookLoan::query()->active()->whereDate('due_on', '<', today())->count(),
        ];
    }

    /** @return Collection<int, BookLoan> Pinjaman lewat tempo, paling lama dulu. */
    public function getTerlambat(): Collection
    {
        return BookLoan::query()->active()
            ->whereDate('due_on', '<', today())
            ->with(['book', 'student.classroom', 'teacher'])
            ->orderBy('due_on')
            ->limit(8)
            ->get();
    }

    /** @return Collection<int, BookLoan> Peminjaman & pengembalian terbaru. */
    public function getAktivitas(): Collection
    {
        return BookLoan::query()
            ->with(['book', 'student', 'teacher'])
            ->latest('updated_at')
            ->limit(8)
            ->get();
    }

    /** Membuka peminjam dari daftar di samping, tanpa mengetik nomornya. */
    public function layani(int $pinjamanId): void
    {
        $pinjaman = BookLoan::query()->with(['student', 'teacher'])->find($pinjamanId);

        if (! $pinjaman) {
            return;
        }

        $this->resetErrorBag();
        $this->jenis = $pinjaman->teacher_id ? 'guru' : 'siswa';
        $this->peminjamId = $pinjaman->teacher_id ?? $pinjaman->student_id;
        $this->nisn = (string) ($pinjaman->teacher?->nip ?? $pinjaman->student?->nisn ?? '');
    }

    public function getKuota(): int
    {
        return BookLoan::KUOTA;
    }
}
