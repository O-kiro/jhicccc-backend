<?php

namespace App\Filament\Resources\CashEntries\Schemas;

use App\Models\CashEntry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CashEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('entry_date')->label('Tanggal')->required()->default(now()),
            Select::make('direction')->label('Arah')->options(CashEntry::ARAH)->required(),
            TextInput::make('category')
                ->label('Kategori')
                ->placeholder('Iuran Komite, Operasional, Kegiatan, …')
                ->required()
                ->maxLength(100),
            TextInput::make('amount')->label('Nominal')->prefix('Rp')->required()->numeric()->minValue(0),
            TextInput::make('description')->label('Uraian')->required()->maxLength(255)->columnSpanFull(),
            TextInput::make('reference')->label('Nomor Bukti')->maxLength(50),
        ]);
    }
}
