<?php

namespace App\Filament\Resources\GuestVisits\Pages;

use App\Filament\Resources\GuestVisits\GuestVisitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGuestVisits extends ListRecords
{
    protected static string $resource = GuestVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
