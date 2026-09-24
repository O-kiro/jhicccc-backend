<?php

namespace App\Filament\Resources\PpdbRegistrants;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\PpdbRegistrants\Pages\CreatePpdbRegistrant;
use App\Filament\Resources\PpdbRegistrants\Pages\EditPpdbRegistrant;
use App\Filament\Resources\PpdbRegistrants\Pages\ListPpdbRegistrants;
use App\Filament\Resources\PpdbRegistrants\Schemas\PpdbRegistrantForm;
use App\Filament\Resources\PpdbRegistrants\Tables\PpdbRegistrantsTable;
use App\Models\PpdbRegistrant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PpdbRegistrantResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = PpdbRegistrant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $modelLabel = 'Pendaftar PPDB';

    protected static ?string $pluralModelLabel = 'Pendaftar PPDB';

    protected static string|\UnitEnum|null $navigationGroup = 'PPDB';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PpdbRegistrantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PpdbRegistrantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPpdbRegistrants::route('/'),
            'create' => CreatePpdbRegistrant::route('/create'),
            'edit' => EditPpdbRegistrant::route('/{record}/edit'),
        ];
    }
}
