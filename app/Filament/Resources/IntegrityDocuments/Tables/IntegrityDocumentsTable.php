<?php

namespace App\Filament\Resources\IntegrityDocuments\Tables;

use App\Models\IntegrityDocument;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class IntegrityDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Dokumen')->wrap()->searchable(),
                TextColumn::make('year')->label('Tahun')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => IntegrityDocument::STATUS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'terverifikasi' => 'success',
                        'terkumpul' => 'info',
                        default => 'gray',
                    }),
                IconColumn::make('file_url')->label('Berkas')->boolean(),
            ])
            // Dikelompokkan per area supaya kelengkapan tiap area langsung
            // terlihat — itu yang dinilai saat evaluasi ZI.
            ->defaultGroup(Group::make('area')->label('Area'))
            ->defaultSort('year', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Status')->options(IntegrityDocument::STATUS),
                SelectFilter::make('area')->label('Area')->options(array_combine(IntegrityDocument::AREA, IntegrityDocument::AREA)),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
