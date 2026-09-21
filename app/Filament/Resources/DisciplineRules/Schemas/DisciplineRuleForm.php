<?php

namespace App\Filament\Resources\DisciplineRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DisciplineRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode')
                    ->placeholder('TT-001')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),

                Select::make('kind')
                    ->label('Jenis')
                    ->options(['pelanggaran' => 'Pelanggaran', 'penghargaan' => 'Penghargaan'])
                    ->default('pelanggaran')
                    ->required(),

                TextInput::make('title')
                    ->label('Uraian')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('category')
                    ->label('Kategori')
                    ->placeholder('Kedisiplinan, Kerapian, Ibadah, …')
                    ->maxLength(100),

                TextInput::make('points')
                    ->label('Bobot Poin')
                    ->helperText('Pelanggaran menambah poin siswa; penghargaan menguranginya.')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1000),

                Toggle::make('is_active')
                    ->label('Berlaku')
                    ->helperText('Aturan nonaktif tidak bisa dipilih saat mencatat kejadian baru.')
                    ->default(true),

                Textarea::make('description')
                    ->label('Keterangan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
