<?php

namespace App\Filament\Resources\AlumniForumCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AlumniForumCategoryForm
{
    /** Ikon yang tersedia di portal (lihat app/components/icons.tsx). */
    private const IKON = [
        'chat' => 'Percakapan',
        'users' => 'Orang',
        'book' => 'Buku',
        'globe' => 'Globe',
        'heart' => 'Hati',
        'sparkle' => 'Kilau',
        'trophy' => 'Piala',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255),
                Select::make('icon')
                    ->label('Ikon')
                    ->helperText('Hanya ikon yang dikenali portal yang tampil benar.')
                    ->options(self::IKON)
                    ->default('chat')
                    ->required(),
                Select::make('tone')
                    ->label('Warna')
                    ->options(['teal' => 'Teal', 'blue' => 'Biru', 'gold' => 'Emas'])
                    ->default('teal')
                    ->required(),
                TextInput::make('sort')
                    ->label('Urutan Tampil')
                    ->numeric()
                    ->default(0),
                Textarea::make('description')
                    ->label('Keterangan')
                    ->required()
                    ->rows(2)
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }
}
