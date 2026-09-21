<?php

namespace App\Filament\Resources\GuestVisits;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\GuestVisits\Pages\CreateGuestVisit;
use App\Filament\Resources\GuestVisits\Pages\EditGuestVisit;
use App\Filament\Resources\GuestVisits\Pages\ListGuestVisits;
use App\Filament\Resources\GuestVisits\Schemas\GuestVisitForm;
use App\Filament\Resources\GuestVisits\Tables\GuestVisitsTable;
use App\Models\GuestVisit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GuestVisitResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = GuestVisit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $modelLabel = 'Kunjungan';

    protected static ?string $pluralModelLabel = 'Buku Tamu';

    protected static string|\UnitEnum|null $navigationGroup = 'Humas';

    protected static ?int $navigationSort = 1;

    /** Tamu yang masih menunggu dilayani. */
    public static function getNavigationBadge(): ?string
    {
        $menunggu = static::getModel()::query()->where('status', 'pending')->count();

        return $menunggu > 0 ? (string) $menunggu : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return GuestVisitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuestVisitsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuestVisits::route('/'),
            'create' => CreateGuestVisit::route('/create'),
            'edit' => EditGuestVisit::route('/{record}/edit'),
        ];
    }
}
