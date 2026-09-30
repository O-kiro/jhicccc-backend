<?php

namespace App\Filament\Resources\Facilities\Schemas;

use App\Filament\Support\PilihanSitus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FacilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')->label('Nama Fasilitas')->required()->maxLength(80),
            PilihanSitus::ikon(),
            Textarea::make('description')->label('Deskripsi')->required()->rows(2)->columnSpanFull(),
            FileUpload::make('image_path')
                ->label('Unggah Foto')
                ->helperText('JPG, PNG, atau WebP, maksimal 2 MB. Bila diisi, ini yang dipakai situs menggantikan petak ikon.')
                ->image()
                ->disk('public')
                ->directory('situs/fasilitas')
                ->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048)
                ->columnSpanFull(),
            TextInput::make('image')
                ->label('atau Jalur Foto yang Sudah Ada')
                ->helperText('Untuk foto yang sudah ada di folder situs, mis. /photos/fasilitas-1.jpg. Diabaikan bila ada unggahan.')
                ->maxLength(255)
                ->columnSpanFull(),
        ]);
    }
}
