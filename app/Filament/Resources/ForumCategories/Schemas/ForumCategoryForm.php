<?php

namespace App\Filament\Resources\ForumCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ForumCategoryForm
{
    /** Ikon yang tersedia di portal siswa (lihat app/components/icons.tsx). */
    private const IKON = [
        'book' => 'Buku',
        'ball' => 'Bola',
        'heart' => 'Hati',
        'chat' => 'Percakapan',
        'users' => 'Orang',
        'flask' => 'Labu',
        'globe' => 'Globe',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    // Slug ikut terisi hanya saat membuat: mengubahnya di
                    // kemudian hari akan memutus tautan yang sudah beredar.
                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create'
                        ? $set('slug', Str::slug((string) $state))
                        : null),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('icon')
                    ->label('Ikon')
                    ->helperText('Hanya ikon yang dikenali portal siswa yang tampil benar.')
                    ->options(self::IKON)
                    ->default('book')
                    ->required(),

                Select::make('tone')
                    ->label('Warna')
                    ->options(['teal' => 'Teal', 'blue' => 'Biru', 'gold' => 'Emas'])
                    ->default('teal')
                    ->required(),

                Textarea::make('description')
                    ->label('Keterangan')
                    ->rows(2)
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }
}
