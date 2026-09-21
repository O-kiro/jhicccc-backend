<?php

namespace App\Filament\Resources\ForumThreads\Pages;

use App\Filament\Resources\ForumThreads\ForumThreadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListForumThreads extends ListRecords
{
    protected static string $resource = ForumThreadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
