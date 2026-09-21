<?php

namespace App\Filament\Resources\ForumThreads\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RepliesRelationManager extends RelationManager
{
    protected static string $relationship = 'replies';

    protected static ?string $title = 'Balasan';

    protected static ?string $modelLabel = 'Balasan';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('body')
                ->label('Isi Balasan')
                ->required()
                ->rows(5)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('student.name')
                    ->label('Penulis')
                    ->searchable(),
                TextColumn::make('body')
                    ->label('Balasan')
                    ->wrap()
                    ->limit(160)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Dikirim')
                    ->dateTime('j M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at')
            // Tanpa tombol tambah: balasan ditulis siswa dari portal.
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
