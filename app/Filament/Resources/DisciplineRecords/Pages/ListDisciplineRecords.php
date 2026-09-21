<?php

namespace App\Filament\Resources\DisciplineRecords\Pages;

use App\Filament\Resources\DisciplineRecords\DisciplineRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDisciplineRecords extends ListRecords
{
    protected static string $resource = DisciplineRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
