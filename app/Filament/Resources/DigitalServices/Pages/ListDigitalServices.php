<?php

namespace App\Filament\Resources\DigitalServices\Pages;

use App\Filament\Resources\DigitalServices\DigitalServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDigitalServices extends ListRecords
{
    protected static string $resource = DigitalServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
