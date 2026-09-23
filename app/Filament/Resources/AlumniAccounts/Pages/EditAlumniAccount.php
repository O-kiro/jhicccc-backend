<?php

namespace App\Filament\Resources\AlumniAccounts\Pages;

use App\Filament\Resources\AlumniAccounts\AlumniAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlumniAccount extends EditRecord
{
    protected static string $resource = AlumniAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
