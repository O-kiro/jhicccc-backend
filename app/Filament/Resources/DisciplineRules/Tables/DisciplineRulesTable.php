<?php

namespace App\Filament\Resources\DisciplineRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DisciplineRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Uraian')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('kind')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'penghargaan' ? 'success' : 'danger'),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('points')
                    ->label('Poin')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Berlaku')
                    ->boolean(),
            ])
            ->defaultSort('code')
            ->filters([
                SelectFilter::make('kind')
                    ->label('Jenis')
                    ->options(['pelanggaran' => 'Pelanggaran', 'penghargaan' => 'Penghargaan']),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
