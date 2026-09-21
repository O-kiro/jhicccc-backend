<?php

namespace App\Filament\Resources\ExamResults\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExamResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('exam.title')
                    ->label('Ujian')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('exam.subject.name')
                    ->label('Mapel')
                    ->badge(),
                TextColumn::make('score')
                    ->label('Nilai')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 75 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->label('Dikumpulkan')
                    ->dateTime('j M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('finished_at', 'desc')
            ->filters([
                SelectFilter::make('exam')
                    ->label('Ujian')
                    ->relationship('exam', 'title'),
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
