<?php

namespace App\Filament\Resources\SitePopups\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SitePopupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('title')->label('Judul')->required()->maxLength(120)->columnSpanFull(),
            Textarea::make('body')->label('Isi')->required()->rows(4)->maxLength(600)->columnSpanFull(),
            TextInput::make('link_url')
                ->label('Tautan Tombol')
                ->helperText('Alamat lengkap atau jalur situs, misalnya /ppdb. Kosongkan bila tanpa tombol.')
                ->maxLength(255),
            TextInput::make('link_label')->label('Teks Tombol')->placeholder('Daftar Sekarang')->maxLength(40),
            DateTimePicker::make('starts_at')
                ->label('Mulai Tayang')
                ->helperText('Kosong berarti langsung tayang.')
                ->seconds(false),
            DateTimePicker::make('ends_at')
                ->label('Berhenti Tayang')
                ->helperText('Kosong berarti tanpa batas.')
                ->seconds(false)
                ->after('starts_at'),
            Toggle::make('is_active')
                ->label('Aktif')
                ->helperText('Bila beberapa pop-up berlaku bersamaan, yang terbaru yang tampil.')
                ->default(true),
        ]);
    }
}
