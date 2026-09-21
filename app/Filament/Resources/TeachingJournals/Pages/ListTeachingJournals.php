<?php

namespace App\Filament\Resources\TeachingJournals\Pages;

use App\Filament\Resources\TeachingJournals\TeachingJournalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTeachingJournals extends ListRecords
{
    protected static string $resource = TeachingJournalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
