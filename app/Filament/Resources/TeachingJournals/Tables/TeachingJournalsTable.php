<?php

namespace App\Filament\Resources\TeachingJournals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class TeachingJournalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('j M Y')
                    ->sortable(),
                TextColumn::make('schedule.classroom.name')
                    ->label('Kelas')
                    ->badge(),
                TextColumn::make('schedule.subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable(),
                TextColumn::make('schedule.starts_at')
                    ->label('Jam')
                    ->formatStateUsing(fn (?string $state): string => $state ? substr($state, 0, 5) : '—'),
                TextColumn::make('teacher.name')
                    ->label('Pengajar')
                    ->placeholder('Sesuai jadwal')
                    ->searchable(),
                TextColumn::make('topic')
                    ->label('Materi')
                    ->wrap()
                    ->limit(70),
                TextColumn::make('present_count')
                    ->label('Hadir')
                    ->numeric()
                    ->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Filter::make('tanggal')
                    ->schema([DatePicker::make('date')->label('Tanggal')])
                    ->query(fn ($query, array $data) => $query->when(
                        $data['date'] ?? null,
                        fn ($q, $tanggal) => $q->whereDate('date', $tanggal),
                    )),
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
