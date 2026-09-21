<?php

namespace App\Filament\Resources\NewsPosts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NewsPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('published_on')->label('Terbit')->date('j M Y')->sortable(),
                TextColumn::make('title')->label('Judul')->searchable()->wrap(),
                TextColumn::make('category')->label('Kategori')->badge(),
                IconColumn::make('is_published')->label('Terbit')->boolean(),
            ])
            ->defaultSort('published_on', 'desc')
            ->filters([TernaryFilter::make('is_published')->label('Status terbit')])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
