<?php

namespace App\Filament\Resources\AssetBookings\Pages;

use App\Filament\Resources\AssetBookings\AssetBookingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssetBooking extends EditRecord
{
    protected static string $resource = AssetBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
