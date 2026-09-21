<?php

namespace App\Filament\Resources\GuestVisits\Tables;

use App\Models\GuestVisit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GuestVisitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('registration_code')->label('Kode')->searchable(),
                TextColumn::make('arrived_at')->label('Datang')->dateTime('j M, H:i')->sortable(),
                TextColumn::make('guest_name')->label('Tamu')->searchable()->sortable(),
                TextColumn::make('institution')->label('Instansi')->placeholder('—')->searchable(),
                TextColumn::make('service.name')->label('Keperluan')->placeholder('—')->wrap(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => GuestVisit::STATUS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'selesai' => 'success',
                        'kunjungan' => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('rating')
                    ->label('Nilai')
                    ->placeholder('—')
                    ->badge()
                    ->toggleable(),
            ])
            ->defaultSort('arrived_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Status')->options(GuestVisit::STATUS),
            ])
            ->recordActions([
                Action::make('selesaikan')
                    ->label('Tandai Selesai')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (GuestVisit $record): bool => $record->status !== 'selesai')
                    ->action(fn (GuestVisit $record) => $record->update([
                        'status' => 'selesai',
                        'finished_at' => $record->finished_at ?? now(),
                    ])),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
