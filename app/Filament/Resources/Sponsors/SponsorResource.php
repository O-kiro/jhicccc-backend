<?php

namespace App\Filament\Resources\Sponsors;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\Sponsors\Pages\CreateSponsor;
use App\Filament\Resources\Sponsors\Pages\EditSponsor;
use App\Filament\Resources\Sponsors\Pages\ListSponsors;
use App\Filament\Resources\Sponsors\Schemas\SponsorForm;
use App\Filament\Resources\Sponsors\Tables\SponsorsTable;
use App\Models\Sponsor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SponsorResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = Sponsor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $modelLabel = 'Mitra';

    protected static ?string $pluralModelLabel = 'Mitra & Pendukung';

    protected static string|\UnitEnum|null $navigationGroup = 'My Website';

    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        return SponsorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SponsorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSponsors::route('/'),
            'create' => CreateSponsor::route('/create'),
            'edit' => EditSponsor::route('/{record}/edit'),
        ];
    }
}
