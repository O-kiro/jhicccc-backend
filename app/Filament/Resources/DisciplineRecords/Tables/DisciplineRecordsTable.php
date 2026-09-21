<?php

namespace App\Filament\Resources\DisciplineRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DisciplineRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_on')
                    ->label('Tanggal')
                    ->date('j M Y')
                    ->sortable(),
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.classroom.name')
                    ->label('Kelas')
                    ->badge(),
                TextColumn::make('rule.title')
                    ->label('Aturan')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('points')
                    ->label('Poin')
                    ->badge()
                    ->color(fn (int $state): string => $state < 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? "+{$state}" : (string) $state)
                    ->sortable(),
                TextColumn::make('teacher.name')
                    ->label('Pencatat')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('occurred_on', 'desc')
            ->filters([
                SelectFilter::make('classroom')
                    ->label('Kelas')
                    ->relationship('student.classroom', 'name'),
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
