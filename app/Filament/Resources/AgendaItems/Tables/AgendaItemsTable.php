<?php

namespace App\Filament\Resources\AgendaItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AgendaItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('date')->label('Tanggal')->date('j M Y')->sortable(),
                TextColumn::make('title')->label('Kegiatan')->searchable()->wrap(),
                TextColumn::make('category')->label('Kategori')->badge(),
            ])
            ->defaultSort('date')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
