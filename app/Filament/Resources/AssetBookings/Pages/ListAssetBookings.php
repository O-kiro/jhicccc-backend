<?php

namespace App\Filament\Resources\AssetBookings\Pages;

use App\Filament\Resources\AssetBookings\AssetBookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssetBookings extends ListRecords
{
    protected static string $resource = AssetBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
