<?php

namespace App\Filament\Resources\SitePopups\Pages;

use App\Filament\Resources\SitePopups\SitePopupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSitePopups extends ListRecords
{
    protected static string $resource = SitePopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
