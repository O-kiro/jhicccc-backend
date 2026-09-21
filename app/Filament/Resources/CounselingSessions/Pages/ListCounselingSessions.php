<?php

namespace App\Filament\Resources\CounselingSessions\Pages;

use App\Filament\Resources\CounselingSessions\CounselingSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCounselingSessions extends ListRecords
{
    protected static string $resource = CounselingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
