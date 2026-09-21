<?php

namespace App\Filament\Resources\Bills\Tables;

use App\Models\Bill;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')->label('Siswa')->searchable()->sortable(),
                TextColumn::make('paymentType.name')->label('Jenis')->searchable(),
                TextColumn::make('period')->label('Periode')->badge()->sortable(),
                TextColumn::make('amount')->label('Tagihan')->money('IDR')->sortable(),
                TextColumn::make('amount_paid')->label('Dibayar')->money('IDR'),
                TextColumn::make('sisa')
                    ->label('Status')
                    ->badge()
                    // Diturunkan dari nominal, bukan kolom status tersendiri —
                    // satu angka tidak bisa bertentangan dengan dirinya sendiri.
                    ->state(fn (Bill $record): string => $record->isPaid()
                        ? 'Lunas'
                        : 'Kurang Rp'.number_format($record->outstanding(), 0, ',', '.'))
                    ->color(fn (string $state): string => $state === 'Lunas' ? 'success' : 'danger'),
                TextColumn::make('due_on')->label('Jatuh Tempo')->date('j M Y')->toggleable(),
            ])
            ->defaultSort('due_on')
            ->filters([
                Filter::make('belum_lunas')
                    ->label('Belum lunas')
                    ->query(fn ($query) => $query->whereColumn('amount_paid', '<', 'amount')),
            ])
            ->recordActions([
                Action::make('lunasi')
                    ->label('Terima Pembayaran')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Menandai tagihan ini lunas penuh dan menerbitkan nomor kuitansi.')
                    ->visible(fn (Bill $record): bool => ! $record->isPaid())
                    ->action(fn (Bill $record) => $record->update([
                        'amount_paid' => $record->amount,
                        'paid_at' => now(),
                        'receipt_no' => $record->receipt_no ?? 'KW-'.Str::upper(Str::random(8)),
                    ])),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
