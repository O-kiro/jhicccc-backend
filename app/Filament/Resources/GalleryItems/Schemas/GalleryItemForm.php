<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Filament\Support\PilihanSitus;
use Filament\Forms\Components\DatePicker;
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
            TextInput::make('image')
                ->label('Foto')
                ->helperText('Jalur foto di situs (mis. /photos/galeri.jpg). Kosong berarti kotak berwarna.')
                ->maxLength(255),
        ]);
    }
}
