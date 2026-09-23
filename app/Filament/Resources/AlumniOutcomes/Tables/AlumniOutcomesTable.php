<?php

namespace App\Filament\Resources\AlumniOutcomes\Tables;

use App\Models\AlumniOutcome;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AlumniOutcomesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year')
                    ->label('Tahun Lulus')
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->state(fn (AlumniOutcome $record): string => $record->label()),
                TextColumn::make('students')
                    ->label('Jumlah Siswa')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('note')
                    ->label('Jalur Populer')
                    ->wrap()
                    ->toggleable(),
            ])
            ->defaultSort('year', 'desc')
            ->filters([
                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(fn (): array => AlumniOutcome::query()
                        ->distinct()->orderByDesc('year')->pluck('year', 'year')->all()),
            ])
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
