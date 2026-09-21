<?php

namespace App\Filament\Resources\Courses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teacher.name')
                    ->label('Pengajar')
                    ->placeholder('Belum ditetapkan')
                    ->searchable(),
                TextColumn::make('classroom.name')
                    ->label('Kelas')
                    ->badge()
                    ->sortable(),
                TextColumn::make('semester')
                    ->label('Semester')
                    ->badge(),
                TextColumn::make('modules_count')
                    ->label('Modul')
                    ->counts('modules')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'warning' : 'success'),
                TextColumn::make('enrollments_count')
                    ->label('Peserta')
                    ->counts('enrollments')
                    ->badge(),
            ])
            ->defaultSort('id')
            ->filters([
                SelectFilter::make('classroom')
                    ->label('Kelas')
                    ->relationship('classroom', 'name'),
                SelectFilter::make('semester')
                    ->options(['Ganjil' => 'Ganjil', 'Genap' => 'Genap']),
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
