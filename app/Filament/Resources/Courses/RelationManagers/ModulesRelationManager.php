<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    protected static ?string $title = 'Modul';

    protected static ?string $modelLabel = 'Modul';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('number')
                ->label('Nomor Urut')
                ->helperText('Menentukan urutan tampil di portal siswa.')
                ->required()
                ->numeric()
                ->minValue(1),

            TextInput::make('title')
                ->label('Judul Modul')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            TextInput::make('url')
                ->label('Tautan Materi')
                ->helperText('Google Drive, YouTube, atau alamat lain. Boleh dikosongkan — portal menampilkan "Materi belum tersedia".')
                ->url()
                ->maxLength(255)
                ->columnSpanFull(),

            Textarea::make('description')
                ->label('Keterangan')
                ->rows(3)
                ->maxLength(1000)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('number')
                    ->label('No.')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->wrap(),
                IconColumn::make('url')
                    ->label('Ada Materi')
                    ->boolean(),
            ])
            ->defaultSort('number')
            ->headerActions([
                CreateAction::make(),
            ])
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
