<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Http\Resources\V1\BookResource as BookApiResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('author')
                    ->label('Penulis')
                    ->helperText('Boleh dikosongkan untuk buku teks tanpa penulis tercantum.')
                    ->maxLength(255),

                Select::make('category')
                    ->label('Kategori')
                    // Daftarnya diambil dari peta ikon/warna API supaya kategori
                    // baru tidak tampil tanpa ikon di portal siswa.
                    ->options(array_combine(
                        array_keys(BookApiResource::TONES),
                        array_keys(BookApiResource::TONES),
                    ))
                    ->required(),

                TextInput::make('total_pages')
                    ->label('Jumlah Halaman')
                    ->required()
                    ->numeric()
                    ->minValue(1),

                TextInput::make('url')
                    ->label('Tautan Berkas')
                    ->helperText('Google Drive, atau alamat lain tempat berkas bukunya dibaca.')
                    ->url()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Sinopsis')
                    ->helperText('Tampil pada kartu "Lanjutkan Membaca" di portal siswa.')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }
}
