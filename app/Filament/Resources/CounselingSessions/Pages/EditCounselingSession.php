<?php

namespace App\Filament\Resources\CounselingSessions\Pages;

use App\Filament\Resources\CounselingSessions\CounselingSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCounselingSession extends EditRecord
{
    protected static string $resource = CounselingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
