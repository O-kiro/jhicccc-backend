<?php

namespace App\Filament\Resources\DigitalServices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DigitalServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')->label('Layanan')->searchable(),
                TextColumn::make('href')->label('Tautan')->color('gray'),
                TextColumn::make('slug')->label('Halaman Detail')->placeholder('—')->prefix('/layanan/'),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
            ])
            ->defaultSort('sort')
            // Seret untuk mengubah urutan tampil di situs.
            ->reorderable('sort')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
