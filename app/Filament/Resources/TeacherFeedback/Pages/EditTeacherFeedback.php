<?php

namespace App\Filament\Resources\TeacherFeedback\Pages;

use App\Filament\Resources\TeacherFeedback\TeacherFeedbackResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTeacherFeedback extends EditRecord
{
    protected static string $resource = TeacherFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
