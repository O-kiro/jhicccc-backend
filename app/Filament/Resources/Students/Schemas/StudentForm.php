<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nisn')
                    ->label('NISN')
                    ->helperText('Dipakai siswa untuk masuk ke portal.')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),

                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                // Wajib hanya saat menambah siswa. Saat mengedit, biarkan kosong
                // untuk mempertahankan kata sandi lama — tanpa dehydrateStateUsing
                // di bawah, string kosong akan ikut tersimpan dan mengunci akun.
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (string $operation): string => $operation === 'create'
                        ? 'Sampaikan kata sandi ini ke siswa yang bersangkutan.'
                        : 'Kosongkan bila tidak ingin mengganti kata sandi.'),

                Select::make('classroom_id')
                    ->label('Kelas')
                    ->relationship('classroom', 'name')
                    ->searchable()
                    ->preload(),

                TextInput::make('streak_days')
                    ->label('Daily Streak (hari)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Akun Aktif')
                    ->helperText('Siswa nonaktif ditolak saat mencoba masuk.')
                    ->default(true),
            ]);
    }
}
