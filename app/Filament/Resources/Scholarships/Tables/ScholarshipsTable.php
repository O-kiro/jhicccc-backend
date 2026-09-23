<?php

namespace App\Filament\Resources\Scholarships\Tables;

use App\Models\Scholarship;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScholarshipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Beasiswa')
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->searchable(),
                TextColumn::make('quota')
                    ->label('Kuota')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Scholarship $record): string => $record->labelStatus())
                    ->color(fn (Scholarship $record): string => match ($record->status) {
                        'dibuka' => 'success',
                        'segera_ditutup' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('deadline')
                    ->label('Batas Akhir')
                    ->date('j M Y')
                    ->sortable(),
                TextColumn::make('verified')
                    ->label('Terverifikasi')
                    ->state(fn (Scholarship $record): string => "{$record->verified} / {$record->applicants}")
                    ->toggleable(),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Scholarship::STATUS),
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
