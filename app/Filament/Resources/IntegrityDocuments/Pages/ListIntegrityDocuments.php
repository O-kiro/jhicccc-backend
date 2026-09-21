<?php

namespace App\Filament\Resources\IntegrityDocuments\Pages;

use App\Filament\Resources\IntegrityDocuments\IntegrityDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIntegrityDocuments extends ListRecords
{
    protected static string $resource = IntegrityDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
