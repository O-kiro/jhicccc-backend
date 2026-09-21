<?php

namespace App\Filament\Resources\Facilities\Schemas;

use App\Filament\Support\PilihanSitus;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FacilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')->label('Nama Fasilitas')->required()->maxLength(80),
            PilihanSitus::ikon(),
            Textarea::make('description')->label('Deskripsi')->required()->rows(2)->columnSpanFull(),
        ]);
    }
}
