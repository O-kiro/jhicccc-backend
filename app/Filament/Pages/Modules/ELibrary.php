<?php

namespace App\Filament\Pages\Modules;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use App\Services\Sirkulasi;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use UnitEnum;

/**
 * Meja sirkulasi: tap kartu siswa, pindai atau pilih buku, lalu proses.
 *
 * Kolom NISN dan kode buku sengaja kolom teks biasa: pembaca RFID maupun
 * barcode "mengetik" kodenya ke kolom yang sedang aktif lalu menekan Enter,
 * jadi alat semacam itu langsung bekerja tanpa integrasi khusus.
 */
class ELibrary extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static string|UnitEnum|null $navigationGroup = 'E-Library';

    protected static ?string $navigationLabel = 'Sirkulasi';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Meja Sirkulasi';

    protected string $view = 'filament.pages.modules.sirkulasi';

    public string $nisn = '';

    public ?int $siswaId = null;

    public string $buku = '';

    public int $durasi = 14;

    /** Membaca kartu siswa. */
    public function cariSiswa(Sirkulasi $sirkulasi): void
    {
        $this->resetErrorBag();

        try {
            $this->siswaId = $sirkulasi->siswa($this->nisn)->id;
        } catch (ValidationException $e) {
            $this->siswaId = null;
            $this->setErrorBag($e->validator->errors());
        }
    }

    public function pinjam(Sirkulasi $sirkulasi): void
    {
        $this->resetErrorBag();
        $siswa = $this->getSiswa();

        if (! $siswa) {
            $this->addError('nisn', 'Tap kartu siswa lebih dulu.');

            return;
        }

        try {
            $pinjaman = $sirkulasi->pinjam($siswa, $sirkulasi->buku($this->buku), $this->durasi);
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
        $pinjaman = BookLoan::query()
            ->whereKey($pinjamanId)
            // Hanya pinjaman milik siswa yang sedang dilayani.
            ->where('student_id', $this->siswaId)
            ->first();

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

    /** Siswa berikutnya: kosongkan meja. */
    public function selesai(): void
    {
        $this->reset(['nisn', 'siswaId', 'buku']);
        $this->resetErrorBag();
    }

    public function getSiswa(): ?Student
    {
        return $this->siswaId ? Student::query()->with('classroom')->find($this->siswaId) : null;
    }

    /** @return Collection<int, BookLoan> */
    public function getPinjamanAktif(): Collection
    {
        return BookLoan::query()
            ->where('student_id', $this->siswaId)
            ->active()
            ->with('book')
            ->orderBy('due_on')
            ->get();
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

    public function getKuota(): int
    {
        return BookLoan::KUOTA;
    }
}
