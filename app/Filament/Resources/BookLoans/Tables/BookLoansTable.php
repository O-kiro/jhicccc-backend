<?php

namespace App\Filament\Resources\BookLoans\Tables;

use App\Models\BookLoan;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class BookLoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('book.title')
                    ->label('Buku')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('due_on')
                    ->label('Jatuh Tempo')
                    ->date('j M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    // Diturunkan, bukan disimpan: satu-satunya sumber kebenaran
                    // tetap returned_at dan due_on.
                    ->state(function (BookLoan $record): string {
                        if ($record->returned_at) {
                            return 'Dikembalikan';
                        }

                        $sisa = $record->daysUntilDue();

                        return $sisa < 0 ? 'Terlambat' : "Sisa {$sisa} hari";
                    })
                    ->color(fn (string $state): string => match (true) {
                        $state === 'Dikembalikan' => 'gray',
                        $state === 'Terlambat' => 'danger',
                        default => 'success',
                    }),
                TextColumn::make('current_page')
                    ->label('Halaman')
                    ->numeric()
                    ->toggleable(),
            ])
            ->defaultSort('due_on')
            ->filters([
                Filter::make('belum_kembali')
                    ->label('Belum dikembalikan')
                    ->query(fn ($query) => $query->whereNull('returned_at'))
                    ->default(),
            ])
            ->recordActions([
                Action::make('kembalikan')
                    ->label('Tandai Kembali')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (BookLoan $record): bool => $record->returned_at === null)
                    ->action(fn (BookLoan $record) => $record->update(['returned_at' => now()])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
