<?php

namespace App\Filament\Resources\Facilities;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\Facilities\Pages\CreateFacility;
use App\Filament\Resources\Facilities\Pages\EditFacility;
use App\Filament\Resources\Facilities\Pages\ListFacilities;
use App\Filament\Resources\Facilities\Schemas\FacilityForm;
use App\Filament\Resources\Facilities\Tables\FacilitiesTable;
use App\Models\Facility;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FacilityResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = Facility::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $modelLabel = 'Fasilitas';

    protected static ?string $pluralModelLabel = 'Fasilitas';

    protected static string|\UnitEnum|null $navigationGroup = 'My Website';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return FacilityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FacilitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacilities::route('/'),
            'create' => CreateFacility::route('/create'),
            'edit' => EditFacility::route('/{record}/edit'),
        ];
    }
}
