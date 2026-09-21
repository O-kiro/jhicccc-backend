<?php

namespace App\Filament\Resources\Letters\Tables;

use App\Models\Letter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LettersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('direction')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Letter::ARAH[$state] ?? $state)
                    ->color(fn (string $state): string => $state === 'masuk' ? 'info' : 'success'),
                TextColumn::make('number')->label('Nomor')->searchable(),
                TextColumn::make('dated_on')->label('Tanggal')->date('j M Y')->sortable(),
                TextColumn::make('subject')->label('Perihal')->wrap()->searchable(),
                TextColumn::make('correspondent')->label('Pengirim / Tujuan')->searchable(),
                IconColumn::make('file_url')->label('Berkas')->boolean(),
            ])
            ->defaultSort('dated_on', 'desc')
            ->filters([SelectFilter::make('direction')->label('Jenis')->options(Letter::ARAH)])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
