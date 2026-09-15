<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                TextInput::make('category')
                    ->label('Kategori')
                    ->required()
                    ->default('Sains'),
                TextInput::make('icon')
                    ->label('Ikon')
                    ->required()
                    ->default('book'),
                TextInput::make('tone')
                    ->label('Warna')
                    ->required()
                    ->default('teal'),
            ]);
    }
}
