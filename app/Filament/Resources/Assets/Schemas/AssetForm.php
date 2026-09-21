<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Models\Asset;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Kode Barang')->required()->unique(ignoreRecord: true)->maxLength(30),
            TextInput::make('name')->label('Nama Barang')->required()->maxLength(255),
            TextInput::make('category')->label('Kategori')->placeholder('Elektronik, Mebel, …')->maxLength(100),
            Select::make('room_id')
                ->label('Lokasi')
                ->relationship('room', 'name')
                ->searchable()
                ->preload(),
            TextInput::make('quantity')->label('Jumlah')->required()->numeric()->minValue(0)->default(1),
            TextInput::make('price')->label('Nilai Perolehan')->prefix('Rp')->numeric()->minValue(0),
            DatePicker::make('acquired_on')->label('Tanggal Perolehan'),
            Select::make('condition')->label('Kondisi')->options(Asset::KONDISI)->default('baik')->required(),
            Textarea::make('note')->label('Catatan')->rows(2)->columnSpanFull(),
        ]);
    }
}
