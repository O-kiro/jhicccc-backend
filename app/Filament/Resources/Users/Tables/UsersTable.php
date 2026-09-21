<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Support\Peran;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Surel')->searchable(),
                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Peran::LABEL[$state] ?? 'Tanpa peran')
                    ->color(fn (?string $state): string => match ($state) {
                        Peran::SUPER_ADMIN => 'danger',
                        null => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('created_at')->label('Dibuat')->date('j M Y')->toggleable(),
            ])
            ->defaultSort('name')
            ->filters([SelectFilter::make('role')->label('Peran')->options(Peran::LABEL)])
            ->recordActions([
                EditAction::make(),
                // Tanpa hapus massal: satu per satu, supaya pengaman di bawah
                // tidak bisa dilewati lewat pilihan banyak baris.
                DeleteAction::make()->hidden(fn (User $record): bool => $record->is(auth()->user())
                    || ($record->role === Peran::SUPER_ADMIN
                        && User::query()->where('role', Peran::SUPER_ADMIN)->count() <= 1)),
            ]);
    }
}
