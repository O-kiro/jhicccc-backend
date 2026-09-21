<?php

namespace App\Filament\Resources\BookLoans\Pages;

use App\Filament\Resources\BookLoans\BookLoanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookLoans extends ListRecords
{
    protected static string $resource = BookLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
