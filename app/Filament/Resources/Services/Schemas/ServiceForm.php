<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Models\Service;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode')
                    ->placeholder('LYN-001')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(30),

                Select::make('kind')
                    ->label('Dipakai Sebagai')
                    ->helperText('Layanan PTSP muncul di permohonan; jenis kunjungan muncul di buku tamu.')
                    ->options(Service::JENIS)
                    ->default('layanan')
                    ->required(),

                TextInput::make('name')
                    ->label('Nama Layanan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('target')
                    ->label('Target Pemohon')
                    ->placeholder('Umum, Wali Murid, Instansi, …')
                    ->maxLength(120),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('requirements')
                    ->label('Syarat & Ketentuan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
