<?php

namespace App\Filament\Resources\Alumnis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AlumnisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('year')->label('Lulus'),
                TextColumn::make('achievement')->label('Capaian')->wrap(),
            ])
            ->defaultSort('sort')
            // Seret untuk mengubah urutan tampil di situs.
            ->reorderable('sort')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
