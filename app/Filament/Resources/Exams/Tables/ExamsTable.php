<?php

namespace App\Filament\Resources\Exams\Tables;

use App\Models\Exam;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                TextColumn::make('subject.name')
                    ->label('Mapel')
                    ->searchable(),
                TextColumn::make('classroom.name')
                    ->label('Kelas')
                    ->badge(),
                TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->dateTime('j M Y, H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    // Diturunkan dari jadwal, bukan disimpan — sama persis
                    // dengan cara /exam-session memutuskan sesi boleh dibuka.
                    ->state(fn (Exam $record): string => match (true) {
                        $record->starts_at->isFuture() => 'Akan datang',
                        $record->ends_at->isFuture() => 'Berlangsung',
                        default => 'Selesai',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Berlangsung' => 'success',
                        'Akan datang' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('questions_count')
                    ->label('Soal')
                    ->counts('questions')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : 'success')
                    ->tooltip('Ujian tanpa soal tidak bisa dikerjakan siswa.'),
                TextColumn::make('results_count')
                    ->label('Dikumpulkan')
                    ->counts('results')
                    ->badge(),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                SelectFilter::make('classroom')
                    ->label('Kelas')
                    ->relationship('classroom', 'name'),
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
