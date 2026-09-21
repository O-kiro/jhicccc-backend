<?php

namespace App\Filament\Resources\ServiceRequests;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\ServiceRequests\Pages\CreateServiceRequest;
use App\Filament\Resources\ServiceRequests\Pages\EditServiceRequest;
use App\Filament\Resources\ServiceRequests\Pages\ListServiceRequests;
use App\Filament\Resources\ServiceRequests\Schemas\ServiceRequestForm;
use App\Filament\Resources\ServiceRequests\Tables\ServiceRequestsTable;
use App\Models\ServiceRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceRequestResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = ServiceRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $modelLabel = 'Permohonan';

    protected static ?string $pluralModelLabel = 'PTSP';

    protected static string|\UnitEnum|null $navigationGroup = 'Humas';

    protected static ?int $navigationSort = 3;

    /** Permohonan yang belum ditutup. */
    public static function getNavigationBadge(): ?string
    {
        $terbuka = static::getModel()::query()->whereIn('status', ['baru', 'diproses'])->count();

        return $terbuka > 0 ? (string) $terbuka : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceRequests::route('/'),
            'create' => CreateServiceRequest::route('/create'),
            'edit' => EditServiceRequest::route('/{record}/edit'),
        ];
    }
}
