<?php

namespace App\Filament\Resources\Schedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('classroom.name')
                    ->label('Kelas')
                    ->searchable(),
                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable(),
                TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->searchable(),
                TextColumn::make('day_of_week')
                    ->label('Hari')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->time()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Selesai')
                    ->time()
                    ->sortable(),
                TextColumn::make('meeting_url')
                    ->label('Tautan Kelas')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
