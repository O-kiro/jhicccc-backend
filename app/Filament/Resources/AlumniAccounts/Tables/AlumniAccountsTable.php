<?php

namespace App\Filament\Resources\AlumniAccounts\Tables;

use App\Filament\Actions\SetelUlangSandi;
use App\Models\AlumniAccount;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AlumniAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('graduation_year')
                    ->label('Angkatan')
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('occupation')
                    ->label('Kegiatan')
                    ->toggleable()
                    ->limit(30),
                TextColumn::make('akses_portal')
                    ->label('Portal Alumni')
                    ->badge()
                    // Sandi tidak pernah ditampilkan; yang terlihat hanya
                    // sudah-tidaknya diberi.
                    ->state(fn (AlumniAccount $record): string => match (true) {
                        ! $record->bisaMasukPortal() => 'Belum diberi',
                        ! $record->is_active => 'Nonaktif',
                        default => 'Aktif',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Nonaktif' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('graduation_year', 'desc')
            ->filters([
                SelectFilter::make('graduation_year')
                    ->label('Angkatan')
                    ->options(fn (): array => AlumniAccount::query()
                        ->distinct()
                        ->orderByDesc('graduation_year')
                        ->pluck('graduation_year', 'graduation_year')
                        ->all()),
            ])
            ->recordActions([
                SetelUlangSandi::make('alumni'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
