<?php

namespace App\Filament\Resources\AssetBookings\Tables;

use App\Models\AssetBooking;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class AssetBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('starts_at')->label('Mulai')->dateTime('j M, H:i')->sortable(),
                TextColumn::make('ends_at')->label('Selesai')->dateTime('j M, H:i'),
                TextColumn::make('objek')
                    ->label('Yang Dipinjam')
                    ->state(fn (AssetBooking $record): string => $record->subjectName())
                    ->description(fn (AssetBooking $record): string => $record->room_id ? 'Ruang' : 'Barang'),
                TextColumn::make('borrower')->label('Peminjam')->searchable(),
                TextColumn::make('purpose')->label('Keperluan')->wrap()->limit(60),
                TextColumn::make('returned_at')
                    ->label('Status')
                    ->badge()
                    ->state(fn (AssetBooking $record): string => match (true) {
                        $record->returned_at !== null => 'Dikembalikan',
                        $record->starts_at->isFuture() => 'Terjadwal',
                        $record->ends_at->isPast() => 'Terlambat kembali',
                        default => 'Dipakai',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Dikembalikan' => 'gray',
                        'Terjadwal' => 'info',
                        'Terlambat kembali' => 'danger',
                        default => 'success',
                    }),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                Filter::make('belum_kembali')
                    ->label('Belum dikembalikan')
                    ->query(fn ($query) => $query->whereNull('returned_at')),
            ])
            ->recordActions([
                Action::make('kembali')
                    ->label('Tandai Kembali')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (AssetBooking $record): bool => $record->returned_at === null)
                    ->action(fn (AssetBooking $record) => $record->update(['returned_at' => now()])),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
