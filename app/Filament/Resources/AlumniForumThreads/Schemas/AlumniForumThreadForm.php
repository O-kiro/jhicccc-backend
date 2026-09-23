<?php

namespace App\Filament\Resources\AlumniForumThreads\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Hanya untuk memoderasi: topik lahir dari portal alumni. Penulisnya tidak
 * bisa diganti — mengubah atribusi tulisan orang lain bukan tugas panel.
 */
class AlumniForumThreadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('alumni_forum_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(150),
                Textarea::make('body')
                    ->label('Isi')
                    ->required()
                    ->rows(6)
                    ->maxLength(5000)
                    ->columnSpanFull(),
            ]);
    }
}
