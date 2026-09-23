<?php

namespace App\Filament\Resources\AlumniForumCategories\Pages;

use App\Filament\Resources\AlumniForumCategories\AlumniForumCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlumniForumCategories extends ListRecords
{
    protected static string $resource = AlumniForumCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
