<?php

namespace App\Filament\Resources\GuestVisits\Pages;

use App\Filament\Resources\GuestVisits\GuestVisitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGuestVisit extends EditRecord
{
    protected static string $resource = GuestVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
