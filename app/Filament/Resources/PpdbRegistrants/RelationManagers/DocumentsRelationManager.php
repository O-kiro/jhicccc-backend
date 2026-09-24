<?php

namespace App\Filament\Resources\PpdbRegistrants\RelationManagers;

use App\Models\PpdbDocument;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Verifikasi berkas pendaftar. Panitia tidak mengunggah dari sini — berkas
 * datang dari calon siswa; yang dilakukan di panel hanya menerima, menolak
 * dengan alasan, atau menghapus.
 */
class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Berkas Pendaftaran';

    protected static ?string $modelLabel = 'Berkas';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('note')
                ->label('Catatan Panitia')
                ->rows(3)
                ->maxLength(1000),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_name')
            ->columns([
                TextColumn::make('jenis')
                    ->label('Jenis Berkas')
                    ->state(fn (PpdbDocument $record): string => $record->labelJenis())
                    ->wrap(),
                TextColumn::make('original_name')
                    ->label('Berkas')
                    ->description(fn (PpdbDocument $record): string => $record->size_kb.' KB')
                    ->url(fn (PpdbDocument $record): string => $record->fileUrl(), true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (PpdbDocument $record): string => $record->labelStatus())
                    ->color(fn (PpdbDocument $record): string => match ($record->status) {
                        'diterima' => 'success',
                        'ditolak' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('note')
                    ->label('Catatan')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Diunggah')
                    ->dateTime('j M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->recordActions([
                Action::make('terima')
                    ->label('Terima')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PpdbDocument $record): bool => $record->status !== 'diterima')
                    ->action(fn (PpdbDocument $record) => $record->update([
                        'status' => 'diterima',
                        'note' => null,
                        'verified_at' => now(),
                        'verified_by' => auth()->id(),
                    ])),
                Action::make('tolak')
                    ->label('Minta Ganti')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (PpdbDocument $record): bool => $record->status !== 'ditolak')
                    ->schema([
                        Textarea::make('note')
                            ->label('Alasan')
                            ->helperText('Alasan ini dibaca calon siswa di halaman unggah berkasnya.')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->action(fn (PpdbDocument $record, array $data) => $record->update([
                        'status' => 'ditolak',
                        'note' => $data['note'],
                        'verified_at' => now(),
                        'verified_by' => auth()->id(),
                    ])),
                DeleteAction::make(),
            ]);
    }
}
