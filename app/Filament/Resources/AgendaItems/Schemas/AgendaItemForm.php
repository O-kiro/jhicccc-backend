<?php

namespace App\Filament\Resources\AgendaItems\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AgendaItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            DatePicker::make('date')->label('Tanggal')->required(),
            Select::make('category')
                ->label('Kategori')
                ->options(['Ujian' => 'Ujian', 'Ekstrakurikuler' => 'Ekstrakurikuler', 'Keagamaan' => 'Keagamaan', 'Umum' => 'Umum'])
                ->required(),
            TextInput::make('title')->label('Kegiatan')->required()->maxLength(160)->columnSpanFull(),
        ]);
    }
}
