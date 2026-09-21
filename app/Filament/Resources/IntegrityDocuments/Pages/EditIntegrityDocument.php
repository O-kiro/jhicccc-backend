<?php

namespace App\Filament\Resources\IntegrityDocuments\Pages;

use App\Filament\Resources\IntegrityDocuments\IntegrityDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIntegrityDocument extends EditRecord
{
    protected static string $resource = IntegrityDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
