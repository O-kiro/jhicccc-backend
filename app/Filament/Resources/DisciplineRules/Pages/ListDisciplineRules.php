<?php

namespace App\Filament\Resources\DisciplineRules\Pages;

use App\Filament\Resources\DisciplineRules\DisciplineRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDisciplineRules extends ListRecords
{
    protected static string $resource = DisciplineRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
