<?php

namespace App\Filament\Resources\SitePopups\Tables;

use App\Models\SitePopup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SitePopupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('title')->label('Judul')->searchable()->wrap(),
                TextColumn::make('tayang')
                    ->label('Status')
                    ->badge()
                    ->state(fn (SitePopup $record): string => match (true) {
                        ! $record->is_active => 'Nonaktif',
                        $record->starts_at?->isFuture() => 'Terjadwal',
                        $record->ends_at?->isPast() => 'Berakhir',
                        default => 'Tayang',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Tayang' => 'success',
                        'Terjadwal' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('starts_at')->label('Mulai')->dateTime('j M Y, H:i')->placeholder('Langsung'),
                TextColumn::make('ends_at')->label('Berhenti')->dateTime('j M Y, H:i')->placeholder('Tanpa batas'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
