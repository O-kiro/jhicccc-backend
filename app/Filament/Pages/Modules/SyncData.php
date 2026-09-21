<?php

namespace App\Filament\Pages\Modules;

use App\Services\ImporSiswa;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

/**
 * Impor data siswa dari berkas ekspor sistem lain.
 *
 * Bukan sinkronisasi langsung: tidak ada sistem pusat yang bisa dihubungi
 * dari sini. Yang disediakan adalah jalur yang memang dipakai sekolah —
 * ekspor dari EMIS atau Excel, lalu unggah di sini.
 */
class SyncData extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?string $navigationLabel = 'Sync Data';

    protected static ?int $navigationSort = 9;

    protected static ?string $title = 'Impor Data Siswa';

    protected string $view = 'filament.pages.modules.sync-data';

    /** @var TemporaryUploadedFile|null */
    public $berkas = null;

    /** @var array<int, array<string, mixed>> */
    public array $baris = [];

    /** @var array<int, string> */
    public array $galat = [];

    /** @var array<int, array{nisn: string, nama: string, kata_sandi: string}> */
    public array $akunBaru = [];

    public ?string $ringkasan = null;

    public function periksa(ImporSiswa $impor): void
    {
        $this->validate(['berkas' => ['required', 'file', 'max:2048', 'mimes:csv,txt']], [
            'berkas.required' => 'Pilih berkas CSV lebih dulu.',
            'berkas.mimes' => 'Berkas harus berformat CSV.',
            'berkas.max' => 'Ukuran berkas maksimal 2 MB.',
        ]);

        $hasil = $impor->periksa((string) file_get_contents($this->berkas->getRealPath()));

        $this->baris = $hasil['baris'];
        $this->galat = $hasil['galat'];
        $this->akunBaru = [];
        $this->ringkasan = null;
    }

    public function terapkan(ImporSiswa $impor): void
    {
        if ($this->jumlah('baru') + $this->jumlah('perbarui') === 0) {
            return;
        }

        $hasil = $impor->terapkan($this->baris);

        $this->akunBaru = $hasil['akun'];
        $this->ringkasan = "{$hasil['baru']} siswa baru, {$hasil['diperbarui']} diperbarui.";
        $this->baris = [];
        $this->berkas = null;

        Notification::make()->title('Impor selesai')->body($this->ringkasan)->success()->send();
    }

    /** Kata sandi siswa baru — hanya bisa diunduh sekali, tepat setelah impor. */
    public function unduhAkun(ImporSiswa $impor): ?StreamedResponse
    {
        if ($this->akunBaru === []) {
            return null;
        }

        $csv = $impor->csvAkun($this->akunBaru);

        return response()->streamDownload(
            fn () => print ($csv),
            'akun-siswa-baru-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv'],
        );
    }

    public function batal(): void
    {
        $this->reset(['berkas', 'baris', 'galat', 'akunBaru', 'ringkasan']);
    }

    public function jumlah(string $aksi): int
    {
        return count(array_filter($this->baris, fn (array $b): bool => $b['aksi'] === $aksi));
    }
}
