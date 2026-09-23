<?php

namespace App\Filament\Resources\AlumniOutcomes\Pages;

use App\Filament\Resources\AlumniOutcomes\AlumniOutcomeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlumniOutcomes extends ListRecords
{
    protected static string $resource = AlumniOutcomeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
