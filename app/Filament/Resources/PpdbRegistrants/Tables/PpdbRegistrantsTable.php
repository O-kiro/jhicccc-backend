<?php

namespace App\Filament\Resources\PpdbRegistrants\Tables;

use App\Models\PpdbRegistrant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PpdbRegistrantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('registration_number')
                    ->label('No. Pendaftaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->description(fn (PpdbRegistrant $record): ?string => $record->origin_school)
                    ->searchable(),
                TextColumn::make('jalur')
                    ->label('Jalur')
                    ->badge(),
                TextColumn::make('documents_count')
                    ->label('Berkas')
                    ->counts('documents')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('kelengkapan')
                    ->label('Kelengkapan')
                    ->badge()
                    // Diturunkan dari berkas yang ada, bukan disimpan: satu
                    // sumber kebenaran tetap tabel berkasnya.
                    ->state(function (PpdbRegistrant $record): string {
                        $wajib = $record->jenisWajib();
                        $ada = $record->documents->pluck('jenis')->all();
                        $kurang = count(array_diff($wajib, $ada));
                        $ditolak = $record->documents->where('status', 'ditolak')->count();

                        return match (true) {
                            $kurang > 0 => "Kurang {$kurang} berkas",
                            $ditolak > 0 => "{$ditolak} perlu diganti",
                            $record->documents->where('status', 'menunggu')->count() > 0 => 'Menunggu verifikasi',
                            default => 'Lengkap & terverifikasi',
                        };
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'Kurang') => 'danger',
                        str_contains($state, 'diganti') => 'warning',
                        str_starts_with($state, 'Menunggu') => 'info',
                        default => 'success',
                    }),
                TextColumn::make('akses')
                    ->label('Akses')
                    ->badge()
                    ->state(fn (PpdbRegistrant $record): string => match (true) {
                        ! $record->bisaMasukPortal() => 'Belum diberi',
                        ! $record->is_active => 'Nonaktif',
                        default => 'Aktif',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Aktif' => 'success',
                        'Nonaktif' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('documents'))
            ->defaultSort('registration_number')
            ->filters([
                SelectFilter::make('jalur')
                    ->label('Jalur')
                    ->options(array_combine(PpdbRegistrant::JALUR, PpdbRegistrant::JALUR)),
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
