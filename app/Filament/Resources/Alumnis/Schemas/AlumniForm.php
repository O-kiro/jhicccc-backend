<?php

namespace App\Filament\Resources\Alumnis\Schemas;

use App\Filament\Support\PilihanSitus;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AlumniForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')->label('Nama')->required()->maxLength(80),
            TextInput::make('year')->label('Tahun Lulus')->required()->numeric()->minValue(1990)->maxValue(2100),
            TextInput::make('achievement')->label('Capaian')->required()->maxLength(160)->columnSpanFull(),
            TextInput::make('field')->label('Bidang')->required()->maxLength(60),
            PilihanSitus::warna(),
            Textarea::make('quote')->label('Kutipan')->required()->rows(3)->columnSpanFull(),
        ]);
    }
}
