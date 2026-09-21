<?php

namespace App\Filament\Resources\Extracurriculars\Schemas;

use App\Filament\Support\PilihanSitus;
use App\Models\Extracurricular;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExtracurricularForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')->label('Nama')->required()->maxLength(80),
            TextInput::make('category')
                ->label('Kategori')
                ->required()
                ->maxLength(40)
                ->datalist(fn (): array => Extracurricular::query()->distinct()->orderBy('category')->pluck('category')->all()),
            PilihanSitus::ikon(),
            Textarea::make('description')->label('Deskripsi')->required()->rows(2)->columnSpanFull(),
        ]);
    }
}
