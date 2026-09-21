<?php

namespace App\Filament\Resources\Achievements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AchievementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('title')->label('Prestasi')->required()->maxLength(160)->columnSpanFull(),
            TextInput::make('student')->label('Siswa / Tim')->required()->maxLength(160),
            Select::make('level')
                ->label('Tingkat')
                ->options(['Kota' => 'Kota', 'Provinsi' => 'Provinsi', 'Nasional' => 'Nasional', 'Internasional' => 'Internasional'])
                ->required(),
            TextInput::make('year')->label('Tahun')->required()->numeric()->minValue(2000)->maxValue(2100)->default((int) now()->format('Y')),
            TextInput::make('field')->label('Bidang')->required()->maxLength(60),
            TextInput::make('organizer')->label('Penyelenggara')->required()->maxLength(160)->columnSpanFull(),
        ]);
    }
}
