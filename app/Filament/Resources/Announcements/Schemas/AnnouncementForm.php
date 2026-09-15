<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                Textarea::make('body')
                    ->label('Isi')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('published_at')
                    ->label('Tanggal Terbit'),
            ]);
    }
}
