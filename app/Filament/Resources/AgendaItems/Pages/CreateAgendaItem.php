<?php

namespace App\Filament\Resources\AgendaItems\Pages;

use App\Filament\Resources\AgendaItems\AgendaItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAgendaItem extends CreateRecord
{
    protected static string $resource = AgendaItemResource::class;
}
