<?php

namespace App\Filament\Resources\DigitalServices\Schemas;

use App\Filament\Resources\DigitalServices\DigitalServiceResource;
use App\Filament\Support\PilihanSitus;
use App\Models\DigitalService;
use Closure;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DigitalServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')->label('Nama Layanan')->required()->maxLength(80),
            PilihanSitus::ikon(),
            PilihanSitus::warna(),
            TextInput::make('href')
                ->label('Tautan Kartu')
                ->helperText('Halaman yang dibuka saat kartu diklik, misalnya /layanan/siswa atau /ppdb.')
                ->required()
                ->maxLength(255),
            Textarea::make('description')->label('Deskripsi Singkat')->required()->rows(2)->maxLength(300)->columnSpanFull(),
            TextInput::make('login_href')->label('Tautan Masuk Sistem')->placeholder('/masuk')->maxLength(255),
            TextInput::make('login_label')->label('Teks Tombol Masuk')->placeholder('Masuk ke Portal Siswa')->maxLength(60),
            Textarea::make('login_note')
                ->label('Catatan di Bawah Tombol Masuk')
                ->placeholder('Gunakan akun resmi madrasah untuk masuk.')
                ->rows(2)
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->label('Tampil di Beranda')
                ->default(true)
                ->rule(fn (?DigitalService $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                    if (! $value) {
                        return;
                    }

                    $aktif = DigitalService::query()
                        ->where('is_active', true)
                        ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                        ->count();

                    if ($aktif >= DigitalServiceResource::MAKS_AKTIF) {
                        $fail('Beranda sudah menampilkan '.DigitalServiceResource::MAKS_AKTIF.' layanan. Nonaktifkan salah satu dulu.');
                    }
                }),

            Section::make('Halaman Detail')
                // Repeater di bawah dimulai kosong (defaultItems(0)). Bawaannya
                // satu baris kosong yang wajib diisi — layanan tanpa halaman
                // detail jadi tidak bisa disimpan karena galat di kolom yang
                // bahkan tersembunyi dalam bagian yang tertutup.
                ->description('Isi bila layanan punya halaman penjelasan sendiri di /layanan/{slug}. Kosongkan slug untuk layanan yang halamannya terpisah, seperti PPDB.')
                ->collapsible()
                ->columnSpanFull()
                ->schema([
                    TextInput::make('slug')
                        ->label('Slug')
                        ->helperText('Bagian akhir alamat halaman detail.')
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->maxLength(60),
                    TextInput::make('full_name')->label('Nama Lengkap')->maxLength(120),
                    TextInput::make('audience')->label('Untuk Siapa')->placeholder('Khusus Siswa MAN Kota Batu')->maxLength(120),
                    Textarea::make('intro')
                        ->label('Pengantar')
                        ->helperText('Kalimat pembuka di kartu judul halaman detail. Kosongkan untuk memakai Deskripsi Singkat.')
                        ->rows(2)
                        ->columnSpanFull(),
                    TextInput::make('guide.label')->label('Tombol Kedua — Teks')->placeholder('Panduan Penggunaan')->maxLength(60),
                    TextInput::make('guide.href')->label('Tombol Kedua — Tautan')->placeholder('#cara-mengakses')->maxLength(255),
                    Repeater::make('about')
                        ->label('Tentang Layanan')
                        ->simple(Textarea::make('paragraf')->rows(3)->required())
                        ->addActionLabel('Tambah paragraf')
                        ->defaultItems(0),
                    Repeater::make('highlights')
                        ->label('Kartu Sorotan')
                        ->helperText('Kartu ringkas di kolom utama halaman detail.')
                        ->schema([
                            TextInput::make('title')->label('Judul')->required(),
                            Textarea::make('desc')->label('Penjelasan')->rows(2)->required(),
                        ])
                        ->addActionLabel('Tambah sorotan')
                        ->defaultItems(0)
                        ->columnSpanFull(),
                    TextInput::make('features_title')
                        ->label('Judul Daftar Fitur')
                        ->placeholder('Fitur Utama')
                        ->helperText('Kosongkan untuk memakai "Fitur Utama".')
                        ->maxLength(80),
                    Repeater::make('features')
                        ->label('Fitur')
                        ->simple(TextInput::make('fitur')->required())
                        ->addActionLabel('Tambah fitur')
                        ->defaultItems(0),
                    Repeater::make('steps')
                        ->label('Langkah Penggunaan')
                        ->schema([
                            TextInput::make('title')->label('Langkah')->required(),
                            Textarea::make('desc')->label('Penjelasan')->rows(2)->required(),
                        ])
                        ->addActionLabel('Tambah langkah')
                        ->defaultItems(0),
                    Textarea::make('note')->label('Catatan')->rows(2)->columnSpanFull(),
                    TextInput::make('help.title')->label('Kartu Bantuan — Judul')->placeholder('Butuh Bantuan IT?')->maxLength(80),
                    TextInput::make('help.label')->label('Kartu Bantuan — Teks Tombol')->placeholder('Bantuan & Contact Support')->maxLength(60),
                    Textarea::make('help.text')
                        ->label('Kartu Bantuan — Isi')
                        ->placeholder('Tim Helpdesk IT Madrasah siap membantu kendala akses akun.')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
