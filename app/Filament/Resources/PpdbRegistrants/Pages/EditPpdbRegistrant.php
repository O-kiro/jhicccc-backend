<?php

namespace App\Filament\Resources\PpdbRegistrants\Pages;

use App\Filament\Resources\PpdbRegistrants\PpdbRegistrantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPpdbRegistrant extends EditRecord
{
    protected static string $resource = PpdbRegistrantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
