<?php

namespace App\Filament\Resources\Sponsors\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SponsorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')
                ->label('Nama Mitra')
                ->helperText('Dipakai juga sebagai teks alternatif logo, jadi tulis nama lengkapnya.')
                ->required()
                ->maxLength(100),

            TextInput::make('url')
                ->label('Tautan Situs')
                ->helperText('Opsional. Kosongkan bila mitra tidak punya situs — logonya tetap tampil, hanya tidak bisa diklik.')
                ->url()
                ->maxLength(255),

            FileUpload::make('logo_path')
                ->label('Unggah Logo')
                ->helperText('PNG berlatar transparan paling rapi. Maksimal 1 MB. Bila diisi, ini yang dipakai situs.')
                ->image()
                ->disk('public')
                ->directory('situs/mitra')
                ->visibility('public')
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                ->maxSize(1024)
                ->columnSpanFull(),

            TextInput::make('logo')
                ->label('atau Jalur Logo yang Sudah Ada')
                ->helperText('Untuk berkas yang sudah ada di folder situs, mis. /photos/mitra-1.png. Diabaikan bila ada unggahan.')
                ->maxLength(255)
                ->columnSpanFull(),

            Toggle::make('is_active')
                ->label('Tampil di Beranda')
                ->helperText('Seksi mitra menyembunyikan diri sendiri bila tidak ada satu pun yang aktif.')
                ->default(true),
        ]);
    }
}
