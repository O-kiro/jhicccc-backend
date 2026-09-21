<?php

namespace App\Filament\Resources\BookLoans\Pages;

use App\Filament\Resources\BookLoans\BookLoanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBookLoan extends EditRecord
{
    protected static string $resource = BookLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
