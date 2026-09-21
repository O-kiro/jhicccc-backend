<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\Peran;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama')->required()->maxLength(255),

            TextInput::make('email')
                ->label('Surel')
                ->helperText('Dipakai untuk masuk di halaman /masuk.')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Select::make('role')
                ->label('Peran')
                ->options(Peran::LABEL)
                ->required()
                ->helperText(fn (?User $record): string => $record?->is(auth()->user())
                    ? 'Peran sendiri tidak bisa diubah, supaya tidak mengunci diri dari panel.'
                    : 'Menentukan menu yang bisa dibuka. Hanya Admin Utama yang bisa mengelola pengguna.')
                // Mengubah peran sendiri bisa mengunci diri keluar dari menu ini.
                ->disabled(fn (?User $record): bool => (bool) $record?->is(auth()->user()))
                ->rule(fn (?User $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                    if ($record && $record->role === Peran::SUPER_ADMIN && $value !== Peran::SUPER_ADMIN
                        && User::query()->where('role', Peran::SUPER_ADMIN)->count() <= 1) {
                        $fail('Ini Admin Utama terakhir. Jadikan akun lain Admin Utama dulu.');
                    }
                }),

            // Wajib hanya saat menambah. Saat menyunting, kosong berarti tidak
            // diganti — tanpa dehydrated() string kosong ikut tersimpan.
            TextInput::make('password')
                ->label('Kata Sandi')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->minLength(8)
                ->helperText(fn (string $operation): string => $operation === 'create'
                    ? 'Minimal 8 karakter. Sampaikan ke pemilik akun.'
                    : 'Kosongkan bila tidak ingin mengganti kata sandi.'),
        ]);
    }
}
