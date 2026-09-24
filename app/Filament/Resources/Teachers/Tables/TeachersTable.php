<?php

namespace App\Filament\Resources\Teachers\Tables;

use App\Filament\Actions\SetelUlangSandi;
use App\Models\Teacher;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeachersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('akses_portal')
                    ->label('Portal Guru')
                    ->badge()
                    // Sandi tidak pernah ditampilkan; yang terlihat hanya
                    // sudah-tidaknya diberi.
                    ->state(fn (Teacher $record): string => match (true) {
                        ! $record->bisaMasukPortal() => 'Belum diberi',
                        ! $record->is_active => 'Nonaktif',
                        default => 'Aktif',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Nonaktif' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                SetelUlangSandi::make('guru'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
