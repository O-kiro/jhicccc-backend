<?php

namespace App\Filament\Resources\AlumniAccounts\Pages;

use App\Filament\Resources\AlumniAccounts\AlumniAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlumniAccounts extends ListRecords
{
    protected static string $resource = AlumniAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
