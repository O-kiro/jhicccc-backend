<?php

namespace App\Filament\Resources\StudentPermits\Pages;

use App\Filament\Resources\StudentPermits\StudentPermitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentPermits extends ListRecords
{
    protected static string $resource = StudentPermitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
