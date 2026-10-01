<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')->label('Nama')->required()->maxLength(80),
            TextInput::make('role')->label('Peran')->placeholder('Wali murid kelas XI')->required()->maxLength(80),
            Textarea::make('quote')->label('Kutipan')->required()->rows(3)->columnSpanFull(),
            FileUpload::make('photo_path')
                ->label('Unggah Foto')
                ->helperText('JPG, PNG, atau WebP, maksimal 2 MB. Bila diisi, ini yang dipakai situs menggantikan inisial nama.')
                ->image()
                ->avatar()
                ->disk('public')
                ->directory('situs/testimoni')
                ->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048)
                ->columnSpanFull(),
            TextInput::make('photo')
                ->label('atau Jalur Foto yang Sudah Ada')
                ->helperText('Untuk foto yang sudah ada di folder situs, mis. /photos/testimoni-1.jpg. Diabaikan bila ada unggahan.')
                ->maxLength(255)
                ->columnSpanFull(),
        ]);
    }
}
