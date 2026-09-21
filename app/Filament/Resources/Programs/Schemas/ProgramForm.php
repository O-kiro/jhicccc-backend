<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Filament\Support\PilihanSitus;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')->label('Nama Program')->required()->maxLength(80),
            TextInput::make('slug')->label('Slug')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(60),
            TextInput::make('tag')->label('Label')->placeholder('Unggulan')->required()->maxLength(40),
            PilihanSitus::ikon(),
            PilihanSitus::warna('color'),
            Textarea::make('description')->label('Deskripsi')->required()->rows(2)->columnSpanFull(),
            Repeater::make('points')->label('Poin Utama')->simple(TextInput::make('poin')->required())->addActionLabel('Tambah poin')->columnSpanFull(),
            Repeater::make('detail')->label('Penjelasan Lengkap')->simple(Textarea::make('paragraf')->rows(3)->required())->addActionLabel('Tambah paragraf')->columnSpanFull(),
            Repeater::make('activities')->label('Kegiatan')->simple(TextInput::make('kegiatan')->required())->addActionLabel('Tambah kegiatan')->columnSpanFull(),
        ]);
    }
}
