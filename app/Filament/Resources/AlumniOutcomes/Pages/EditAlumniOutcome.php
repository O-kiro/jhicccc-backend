<?php

namespace App\Filament\Resources\AlumniOutcomes\Pages;

use App\Filament\Resources\AlumniOutcomes\AlumniOutcomeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlumniOutcome extends EditRecord
{
    protected static string $resource = AlumniOutcomeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
