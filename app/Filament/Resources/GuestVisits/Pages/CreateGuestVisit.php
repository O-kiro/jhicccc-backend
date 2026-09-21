<?php

namespace App\Filament\Resources\GuestVisits\Pages;

use App\Filament\Resources\GuestVisits\GuestVisitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuestVisit extends CreateRecord
{
    protected static string $resource = GuestVisitResource::class;
}
