<?php

namespace App\Filament\Resources\Rooms\Schemas;

use App\Models\Room;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Kode')->required()->unique(ignoreRecord: true)->maxLength(30),
            TextInput::make('name')->label('Nama Ruang')->required()->maxLength(255),
            TextInput::make('type')->label('Jenis')->placeholder('Kelas, Laboratorium, Kantor, …')->maxLength(100),
            TextInput::make('capacity')->label('Kapasitas')->numeric()->minValue(0),
            Select::make('condition')->label('Kondisi')->options(Room::KONDISI)->default('baik')->required(),
            Textarea::make('note')->label('Catatan')->rows(2)->columnSpanFull(),
        ]);
    }
}
