<?php

namespace App\Filament\Resources\StudentPermits\Tables;

use App\Models\StudentPermit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentPermitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('left_at')
                    ->label('Keluar')
                    ->dateTime('j M, H:i')
                    ->sortable(),
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.classroom.name')
                    ->label('Kelas')
                    ->badge(),
                TextColumn::make('kind')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => StudentPermit::JENIS[$state] ?? $state),
                TextColumn::make('reason')
                    ->label('Keperluan')
                    ->wrap()
                    ->limit(60),
                TextColumn::make('returned_at')
                    ->label('Kembali')
                    ->dateTime('H:i')
                    ->placeholder('Masih di luar')
                    ->badge()
                    ->color(fn (?string $state): string => $state ? 'success' : 'warning'),
            ])
            ->defaultSort('left_at', 'desc')
            ->filters([
                Filter::make('masih_di_luar')
                    ->label('Belum kembali')
                    ->query(fn ($query) => $query->whereNull('returned_at')),
                SelectFilter::make('kind')
                    ->label('Jenis')
                    ->options(StudentPermit::JENIS),
            ])
            ->recordActions([
                Action::make('kembali')
                    ->label('Tandai Kembali')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (StudentPermit $record): bool => $record->returned_at === null)
                    ->action(fn (StudentPermit $record) => $record->update(['returned_at' => now()])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
