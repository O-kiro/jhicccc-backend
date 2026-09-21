<?php

namespace App\Filament\Resources\StudentPermits\Pages;

use App\Filament\Resources\StudentPermits\StudentPermitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentPermit extends EditRecord
{
    protected static string $resource = StudentPermitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
