<?php

namespace App\Filament\Resources\AssetBookings;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\AssetBookings\Pages\CreateAssetBooking;
use App\Filament\Resources\AssetBookings\Pages\EditAssetBooking;
use App\Filament\Resources\AssetBookings\Pages\ListAssetBookings;
use App\Filament\Resources\AssetBookings\Schemas\AssetBookingForm;
use App\Filament\Resources\AssetBookings\Tables\AssetBookingsTable;
use App\Models\AssetBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssetBookingResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = AssetBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $modelLabel = 'Peminjaman';

    protected static ?string $pluralModelLabel = 'Peminjaman & Booking';

    protected static string|\UnitEnum|null $navigationGroup = 'Sarana & Prasarana';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return AssetBookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetBookingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssetBookings::route('/'),
            'create' => CreateAssetBooking::route('/create'),
            'edit' => EditAssetBooking::route('/{record}/edit'),
        ];
    }
}
