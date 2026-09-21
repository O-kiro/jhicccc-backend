<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Models\Asset;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('category')->label('Kategori')->badge()->placeholder('—'),
                TextColumn::make('room.name')->label('Lokasi')->placeholder('Belum ditempatkan')->searchable(),
                TextColumn::make('quantity')->label('Jumlah')->numeric()->sortable(),
                TextColumn::make('price')
                    ->label('Nilai')
                    ->money('IDR')
                    ->placeholder('—')
                    ->summarize(Sum::make()->label('Total nilai')->money('IDR')),
                TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Asset::KONDISI[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'baik' => 'success',
                        'rusak_ringan' => 'warning',
                        default => 'danger',
                    }),
            ])
            ->defaultSort('code')
            ->filters([
                SelectFilter::make('condition')->label('Kondisi')->options(Asset::KONDISI),
                SelectFilter::make('room')->label('Lokasi')->relationship('room', 'name'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
