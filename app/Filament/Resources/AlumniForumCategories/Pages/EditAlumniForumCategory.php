<?php

namespace App\Filament\Resources\AlumniForumCategories\Pages;

use App\Filament\Resources\AlumniForumCategories\AlumniForumCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlumniForumCategory extends EditRecord
{
    protected static string $resource = AlumniForumCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
