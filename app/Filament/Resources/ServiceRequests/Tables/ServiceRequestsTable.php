<?php

namespace App\Filament\Resources\ServiceRequests\Tables;

use App\Models\ServiceRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServiceRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket')->label('Tiket')->searchable(),
                TextColumn::make('submitted_at')->label('Diajukan')->dateTime('j M Y, H:i')->sortable(),
                TextColumn::make('applicant_name')->label('Pemohon')->searchable()->sortable(),
                TextColumn::make('service.name')->label('Layanan')->wrap()->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ServiceRequest::STATUS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'selesai' => 'success',
                        'ditolak' => 'danger',
                        'diproses' => 'info',
                        default => 'warning',
                    }),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Status')->options(ServiceRequest::STATUS),
            ])
            ->recordActions([
                Action::make('selesaikan')
                    ->label('Tandai Selesai')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ServiceRequest $record): bool => ! in_array($record->status, ['selesai', 'ditolak'], true))
                    ->action(fn (ServiceRequest $record) => $record->update([
                        'status' => 'selesai',
                        'completed_at' => now(),
                    ])),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
