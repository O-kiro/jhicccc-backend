<?php

namespace App\Filament\Resources\CashEntries\Tables;

use App\Models\CashEntry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CashEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_date')->label('Tanggal')->date('j M Y')->sortable(),
                TextColumn::make('direction')
                    ->label('Arah')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => CashEntry::ARAH[$state] ?? $state)
                    ->color(fn (string $state): string => $state === 'masuk' ? 'success' : 'danger'),
                TextColumn::make('category')->label('Kategori')->badge()->searchable(),
                TextColumn::make('description')->label('Uraian')->wrap()->searchable(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->money('IDR')),
            ])
            ->defaultSort('entry_date', 'desc')
            ->filters([
                SelectFilter::make('direction')->label('Arah')->options(CashEntry::ARAH),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
