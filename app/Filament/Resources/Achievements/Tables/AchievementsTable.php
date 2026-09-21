<?php

namespace App\Filament\Resources\Achievements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AchievementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('title')->label('Prestasi')->searchable()->wrap(),
                TextColumn::make('student')->label('Siswa')->searchable(),
                TextColumn::make('level')->label('Tingkat')->badge(),
                TextColumn::make('year')->label('Tahun'),
            ])
            ->defaultSort('sort')
            // Seret untuk mengubah urutan tampil di situs.
            ->reorderable('sort')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
