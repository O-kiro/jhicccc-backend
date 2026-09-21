<?php

namespace App\Filament\Resources\CounselingSessions\Tables;

use App\Models\CounselingSession;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CounselingSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('held_on')->label('Tanggal')->date('j M Y')->sortable(),
                TextColumn::make('student.name')->label('Siswa')->searchable()->sortable(),
                TextColumn::make('student.classroom.name')->label('Kelas')->badge(),
                TextColumn::make('category')->label('Bidang')->badge(),
                TextColumn::make('summary')
                    ->label('Ringkasan')
                    ->wrap()
                    ->limit(80)
                    // Isi catatan rahasia tidak ikut tampil di daftar.
                    ->formatStateUsing(fn (string $state, CounselingSession $record): string => $record->is_confidential
                        ? '(rahasia — buka catatan untuk membaca)'
                        : $state),
                IconColumn::make('is_confidential')->label('Rahasia')->boolean(),
                TextColumn::make('teacher.name')->label('Guru BK')->placeholder('—')->toggleable(),
            ])
            ->defaultSort('held_on', 'desc')
            ->filters([
                SelectFilter::make('category')->label('Bidang')->options([
                    'Akademik' => 'Akademik', 'Pribadi' => 'Pribadi', 'Sosial' => 'Sosial', 'Karier' => 'Karier',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
