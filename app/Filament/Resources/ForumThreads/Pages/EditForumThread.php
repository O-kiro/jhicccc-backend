<?php

namespace App\Filament\Resources\ForumThreads\Pages;

use App\Filament\Resources\ForumThreads\ForumThreadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditForumThread extends EditRecord
{
    protected static string $resource = ForumThreadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
