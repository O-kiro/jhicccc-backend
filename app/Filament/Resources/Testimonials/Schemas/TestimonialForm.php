<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')->label('Nama')->required()->maxLength(80),
            TextInput::make('role')->label('Peran')->placeholder('Wali murid kelas XI')->required()->maxLength(80),
            Textarea::make('quote')->label('Kutipan')->required()->rows(3)->columnSpanFull(),
        ]);
    }
}
