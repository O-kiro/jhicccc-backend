<?php

namespace App\Filament\Resources\Rooms\Tables;

use App\Models\Room;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('type')->label('Jenis')->badge()->placeholder('—'),
                TextColumn::make('capacity')->label('Kapasitas')->numeric()->placeholder('—'),
                TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Room::KONDISI[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'baik' => 'success',
                        'rusak_ringan' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('assets_count')->label('Barang')->counts('assets')->badge(),
            ])
            ->defaultSort('code')
            ->filters([SelectFilter::make('condition')->label('Kondisi')->options(Room::KONDISI)])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
