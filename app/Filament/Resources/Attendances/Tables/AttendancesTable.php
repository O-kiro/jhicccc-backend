<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
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
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state.' — '.(Attendance::KODE[$state] ?? '?'))
                    ->color(fn (string $state): string => match ($state) {
                        'H' => 'success',
                        'T' => 'warning',
                        'A' => 'danger',
                        'L' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('check_in_at')
                    ->label('Jam Masuk')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('source')
                    ->label('Sumber')
                    ->formatStateUsing(fn (string $state): string => Attendance::SUMBER[$state] ?? $state)
                    ->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('code')
                    ->label('Kode')
                    ->options(Attendance::KODE),
                SelectFilter::make('classroom')
                    ->label('Kelas')
                    ->relationship('student.classroom', 'name'),
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
