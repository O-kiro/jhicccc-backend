<?php

namespace App\Filament\Resources\PpdbRegistrants\Pages;

use App\Filament\Resources\PpdbRegistrants\PpdbRegistrantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPpdbRegistrants extends ListRecords
{
    protected static string $resource = PpdbRegistrantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
