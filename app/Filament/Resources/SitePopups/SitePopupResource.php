<?php

namespace App\Filament\Resources\SitePopups;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\SitePopups\Pages\CreateSitePopup;
use App\Filament\Resources\SitePopups\Pages\EditSitePopup;
use App\Filament\Resources\SitePopups\Pages\ListSitePopups;
use App\Filament\Resources\SitePopups\Schemas\SitePopupForm;
use App\Filament\Resources\SitePopups\Tables\SitePopupsTable;
use App\Models\SitePopup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SitePopupResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = SitePopup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $modelLabel = 'Pop-up';

    protected static ?string $pluralModelLabel = 'Tampilan & Pop-up';

    protected static string|\UnitEnum|null $navigationGroup = 'My Website';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SitePopupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SitePopupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSitePopups::route('/'),
            'create' => CreateSitePopup::route('/create'),
            'edit' => EditSitePopup::route('/{record}/edit'),
        ];
    }
}
