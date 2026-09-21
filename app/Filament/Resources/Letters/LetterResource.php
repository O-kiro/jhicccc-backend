<?php

namespace App\Filament\Resources\Letters;

use App\Filament\Resources\Letters\Pages\CreateLetter;
use App\Filament\Resources\Letters\Pages\EditLetter;
use App\Filament\Resources\Letters\Pages\ListLetters;
use App\Filament\Resources\Letters\Schemas\LetterForm;
use App\Filament\Resources\Letters\Tables\LettersTable;
use App\Models\Letter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LetterResource extends Resource
{
    protected static ?string $model = Letter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $modelLabel = 'Surat';

    protected static ?string $pluralModelLabel = 'Persuratan';

    protected static string|\UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return LetterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LettersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLetters::route('/'),
            'create' => CreateLetter::route('/create'),
            'edit' => EditLetter::route('/{record}/edit'),
        ];
    }
}
