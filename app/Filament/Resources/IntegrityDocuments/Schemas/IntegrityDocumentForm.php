<?php

namespace App\Filament\Resources\IntegrityDocuments\Schemas;

use App\Models\IntegrityDocument;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IntegrityDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('area')
                ->label('Area Perubahan')
                ->options(array_combine(IntegrityDocument::AREA, IntegrityDocument::AREA))
                ->required(),
            TextInput::make('year')
                ->label('Tahun')
                ->required()
                ->numeric()
                ->minValue(2000)
                ->maxValue(2100)
                ->default((int) now()->format('Y')),
            TextInput::make('title')->label('Nama Dokumen')->required()->maxLength(255)->columnSpanFull(),
            TextInput::make('file_url')->label('Tautan Berkas')->url()->maxLength(255)->columnSpanFull(),
            Select::make('status')->label('Status')->options(IntegrityDocument::STATUS)->default('draf')->required(),
            Textarea::make('description')->label('Keterangan')->rows(3)->columnSpanFull(),
        ]);
    }
}
