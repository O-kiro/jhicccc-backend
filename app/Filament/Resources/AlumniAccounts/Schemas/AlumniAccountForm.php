<?php

namespace App\Filament\Resources\AlumniAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AlumniAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('graduation_year')
                            ->label('Tahun Lulus')
                            ->required()
                            ->numeric()
                            ->minValue(1980)
                            ->maxValue((int) now()->year + 1)
                            ->default((int) now()->year),
                        TextInput::make('email')
                            ->label('Email')
                            ->helperText('Dipakai alumni untuk masuk ke portal.')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('occupation')
                            ->label('Kegiatan Sekarang')
                            ->helperText('Mis. "Mahasiswa Kedokteran UB" atau "Engineer di Telkom".')
                            ->maxLength(255),
                    ]),

                Section::make('Akses Portal Alumni')
                    ->columns(2)
                    ->schema([
                        // Kosong berarti belum diberi akses. Saat mengedit,
                        // kosong berarti sandi lama dipertahankan.
                        TextInput::make('password')
                            ->label('Kata Sandi Portal')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'Isi untuk memberi akses portal, lalu sampaikan ke alumni yang bersangkutan.'
                                : 'Kosongkan bila tidak ingin mengganti kata sandi.'),
                        Toggle::make('is_active')
                            ->label('Akun Aktif')
                            ->helperText('Menonaktifkan akun ikut mengeluarkan alumni dari semua perangkat.')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
