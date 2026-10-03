<?php

namespace App\Filament\Resources\Achievements\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AchievementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('title')->label('Prestasi')->required()->maxLength(160)->columnSpanFull(),
            TextInput::make('student')->label('Siswa / Tim')->required()->maxLength(160),
            Select::make('level')
                ->label('Tingkat')
                ->options(['Kota' => 'Kota', 'Provinsi' => 'Provinsi', 'Nasional' => 'Nasional', 'Internasional' => 'Internasional'])
                ->required(),
            TextInput::make('year')->label('Tahun')->required()->numeric()->minValue(2000)->maxValue(2100)->default((int) now()->format('Y')),
            TextInput::make('field')->label('Bidang')->required()->maxLength(60),
            TextInput::make('organizer')->label('Penyelenggara')->required()->maxLength(160)->columnSpanFull(),

            FileUpload::make('image_path')
                ->label('Unggah Foto Bukti')
                ->helperText('Sertifikat, medali, atau dokumentasi penyerahan. JPG, PNG, atau WebP, maksimal 2 MB. Opsional — kartu tanpa foto tetap tampil ringkas.')
                ->image()
                ->disk('public')
                ->directory('situs/prestasi')
                ->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048)
                ->columnSpanFull(),

            TextInput::make('image')
                ->label('atau Jalur Foto yang Sudah Ada')
                ->helperText('Untuk foto yang sudah ada di folder situs, mis. /photos/prestasi-1.jpg. Diabaikan bila ada unggahan.')
                ->maxLength(255)
                ->columnSpanFull(),
        ]);
    }
}
