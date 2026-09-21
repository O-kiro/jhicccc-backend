<?php

namespace App\Filament\Resources\DisciplineRecords\Pages;

use App\Filament\Resources\DisciplineRecords\DisciplineRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDisciplineRecord extends EditRecord
{
    protected static string $resource = DisciplineRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
