<?php

namespace App\Filament\Resources\ReportCards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportCardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable(),
                TextColumn::make('academic_year')
                    ->label('Tahun Pelajaran')
                    ->searchable(),
                TextColumn::make('semester')
                    ->label('Semester')
                    ->searchable(),
                TextColumn::make('average_score')
                    ->label('Rata-Rata')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('class_rank')
                    ->label('Peringkat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('class_size')
                    ->label('Jumlah Siswa')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('attendance_percentage')
                    ->label('Kehadiran (%)')
                    ->numeric()
                    ->sortable(),
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
