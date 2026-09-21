<?php

namespace App\Filament\Resources\AssetBookings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssetBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        // Tepat satu yang dipinjam: ruang atau barang. requiredWithout
        // menolak keduanya kosong, prohibits menolak keduanya terisi —
        // kalau tidak, laporan pemakaian ruang dan barang saling tumpang tindih.
        return $schema->components([
            Select::make('room_id')
                ->label('Ruang')
                ->relationship('room', 'name')
                ->searchable()
                ->preload()
                ->live()
                ->requiredWithout('asset_id')
                ->prohibits('asset_id')
                ->helperText('Isi salah satu: ruang atau barang.'),

            Select::make('asset_id')
                ->label('Barang')
                ->relationship('asset', 'name')
                ->searchable()
                ->preload()
                ->live()
                ->requiredWithout('room_id')
                ->prohibits('room_id'),

            TextInput::make('borrower')->label('Peminjam')->required()->maxLength(255),

            DateTimePicker::make('starts_at')->label('Mulai')->seconds(false)->required(),

            DateTimePicker::make('ends_at')->label('Selesai')->seconds(false)->required()->after('starts_at'),

            DateTimePicker::make('returned_at')
                ->label('Dikembalikan')
                ->helperText('Kosongkan selama masih dipakai.')
                ->seconds(false),

            Textarea::make('purpose')->label('Keperluan')->required()->rows(2)->columnSpanFull(),
        ]);
    }
}
