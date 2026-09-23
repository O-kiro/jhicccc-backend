<?php

namespace App\Filament\Resources\AlumniForumThreads\Pages;

use App\Filament\Resources\AlumniForumThreads\AlumniForumThreadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlumniForumThreads extends ListRecords
{
    protected static string $resource = AlumniForumThreadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
