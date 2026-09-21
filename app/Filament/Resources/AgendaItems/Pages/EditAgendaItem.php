<?php

namespace App\Filament\Resources\AgendaItems\Pages;

use App\Filament\Resources\AgendaItems\AgendaItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAgendaItem extends EditRecord
{
    protected static string $resource = AgendaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
