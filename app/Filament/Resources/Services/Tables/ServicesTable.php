<?php

namespace App\Filament\Resources\Services\Tables;

use App\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama')->wrap()->searchable(),
                TextColumn::make('kind')
                    ->label('Dipakai')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Service::JENIS[$state] ?? $state),
                TextColumn::make('target')->label('Target')->placeholder('Umum'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->defaultSort('code')
            ->filters([
                SelectFilter::make('kind')->label('Dipakai Sebagai')->options(Service::JENIS),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
