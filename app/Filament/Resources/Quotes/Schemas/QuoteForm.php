<?php

namespace App\Filament\Resources\Quotes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('body')
                    ->label('Isi Kutipan')
                    ->helperText('Tampil pada sapaan halaman Overview portal siswa.')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),

                TextInput::make('source')
                    ->label('Sumber')
                    ->placeholder('HR. Muslim, QS. Al-Mujadalah: 11, …')
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Kutipan berganti tiap hari, dipilih bergiliran dari yang aktif.')
                    ->default(true),
            ]);
    }
}
