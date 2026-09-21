<?php

namespace App\Filament\Resources\ForumCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ForumCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Keterangan')
                    ->wrap()
                    ->limit(80)
                    ->placeholder('—'),
                TextColumn::make('tone')
                    ->label('Warna')
                    ->badge(),
                TextColumn::make('threads_count')
                    ->label('Topik')
                    ->counts('threads')
                    ->badge(),
            ])
            ->defaultSort('id')
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
