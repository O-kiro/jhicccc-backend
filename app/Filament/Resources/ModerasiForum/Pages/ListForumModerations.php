<?php

namespace App\Filament\Resources\ModerasiForum\Pages;

use App\Filament\Resources\ModerasiForum\ForumModerationResource;
use Filament\Resources\Pages\ListRecords;

class ListForumModerations extends ListRecords
{
    protected static string $resource = ForumModerationResource::class;
}
