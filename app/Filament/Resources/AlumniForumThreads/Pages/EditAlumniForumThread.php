<?php

namespace App\Filament\Resources\AlumniForumThreads\Pages;

use App\Filament\Resources\AlumniForumThreads\AlumniForumThreadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlumniForumThread extends EditRecord
{
    protected static string $resource = AlumniForumThreadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
