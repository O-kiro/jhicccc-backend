<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Filament\Support\PilihanSitus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('title')->label('Judul')->required()->maxLength(120)->columnSpanFull(),
            DatePicker::make('date')->label('Tanggal Kegiatan')->required(),
            TextInput::make('category')->label('Kategori')->required()->maxLength(40),
            PilihanSitus::warna(),
            FileUpload::make('image_path')
                ->label('Unggah Gambar')
                ->helperText('JPG, PNG, atau WebP, maksimal 2 MB. Bila diisi, ini yang dipakai situs.')
                ->image()
                ->disk('public')
                ->directory('situs/galeri')
                ->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048)
                ->columnSpanFull(),
            TextInput::make('image')
                ->label('atau Jalur Foto yang Sudah Ada')
                ->helperText('Untuk foto yang sudah ada di folder situs, mis. /photos/berkas.jpg. Diabaikan bila ada unggahan.')
                ->maxLength(255),
        ]);
    }
}
