<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeacherForm
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
                            ->helperText('Lengkap dengan gelar, seperti tertulis di rapor.')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('nip')
                            ->label('NIP')
                            ->unique(ignoreRecord: true)
                            ->maxLength(30),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),

                Section::make('Akses Portal Guru')
                    ->description('Guru masuk di halaman yang sama dengan siswa, memakai email atau NIP di atas.')
                    ->columns(2)
                    ->schema([
                        // Tidak wajib: guru tanpa sandi memang belum diberi
                        // akses. Saat mengedit, kosong berarti sandi lama
                        // dipertahankan — tanpa dehydrated() di bawah, string
                        // kosong ikut tersimpan.
                        TextInput::make('password')
                            ->label('Kata Sandi Portal')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'Isi untuk memberi akses portal, lalu sampaikan ke guru yang bersangkutan.'
                                : 'Kosongkan bila tidak ingin mengganti kata sandi.'),
                        Toggle::make('is_active')
                            ->label('Akun Aktif')
                            ->helperText('Menonaktifkan akun ikut mengeluarkan guru dari semua perangkat.')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
