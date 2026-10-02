<?php

namespace App\Filament\Resources\Sponsors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SponsorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('logo_path')->label('Logo')->disk('public')->height(40),
                TextColumn::make('name')->label('Nama Mitra')->searchable(),
                TextColumn::make('url')->label('Tautan')->limit(40)->placeholder('—'),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
            ])
            ->defaultSort('sort')
            // Seret untuk mengubah urutan tampil di situs.
            ->reorderable('sort')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
