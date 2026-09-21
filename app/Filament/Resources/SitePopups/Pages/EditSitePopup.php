<?php

namespace App\Filament\Resources\SitePopups\Pages;

use App\Filament\Resources\SitePopups\SitePopupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSitePopup extends EditRecord
{
    protected static string $resource = SitePopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
