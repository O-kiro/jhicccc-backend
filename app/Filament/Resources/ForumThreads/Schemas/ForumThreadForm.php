<?php

namespace App\Filament\Resources\ForumThreads\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ForumThreadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Penulis')
                    ->relationship('student', 'name')
                    // Penulis tidak boleh diubah: itu memalsukan siapa yang
                    // menulis sebuah topik.
                    ->disabled()
                    ->dehydrated(false),

                Select::make('forum_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required(),

                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(150)
                    ->columnSpanFull(),

                Textarea::make('body')
                    ->label('Isi')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }
}
