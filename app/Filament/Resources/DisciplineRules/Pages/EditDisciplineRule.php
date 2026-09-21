<?php

namespace App\Filament\Resources\DisciplineRules\Pages;

use App\Filament\Resources\DisciplineRules\DisciplineRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDisciplineRule extends EditRecord
{
    protected static string $resource = DisciplineRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
