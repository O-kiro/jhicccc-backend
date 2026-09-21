<?php

namespace App\Filament\Resources\DigitalServices\Pages;

use App\Filament\Resources\DigitalServices\DigitalServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDigitalService extends EditRecord
{
    protected static string $resource = DigitalServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
