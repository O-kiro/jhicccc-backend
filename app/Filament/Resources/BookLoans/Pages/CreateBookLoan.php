<?php

namespace App\Filament\Resources\BookLoans\Pages;

use App\Filament\Resources\BookLoans\BookLoanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBookLoan extends CreateRecord
{
    protected static string $resource = BookLoanResource::class;
}
