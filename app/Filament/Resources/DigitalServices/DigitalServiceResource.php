<?php

namespace App\Filament\Resources\DigitalServices;

use App\Filament\Resources\DigitalServices\Pages\CreateDigitalService;
use App\Filament\Resources\DigitalServices\Pages\EditDigitalService;
use App\Filament\Resources\DigitalServices\Pages\ListDigitalServices;
use App\Filament\Resources\DigitalServices\Schemas\DigitalServiceForm;
use App\Filament\Resources\DigitalServices\Tables\DigitalServicesTable;
use App\Models\DigitalService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DigitalServiceResource extends Resource
{
    protected static ?string $model = DigitalService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $modelLabel = 'Layanan';

    protected static ?string $pluralModelLabel = 'Layanan Cepat';

    protected static string|\UnitEnum|null $navigationGroup = 'My Website';

    protected static ?int $navigationSort = 2;

    /** Beranda hanya punya tempat untuk delapan kartu layanan. */
    public const MAKS_AKTIF = 8;

    public static function form(Schema $schema): Schema
    {
        return DigitalServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DigitalServicesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDigitalServices::route('/'),
            'create' => CreateDigitalService::route('/create'),
            'edit' => EditDigitalService::route('/{record}/edit'),
        ];
    }
}
