<?php

namespace App\Filament\Resources\TeachingJournals\Pages;

use App\Filament\Resources\TeachingJournals\TeachingJournalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTeachingJournal extends EditRecord
{
    protected static string $resource = TeachingJournalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
