<?php

namespace App\Filament\Resources\TeacherFeedback\Pages;

use App\Filament\Resources\TeacherFeedback\TeacherFeedbackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTeacherFeedback extends ListRecords
{
    protected static string $resource = TeacherFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
