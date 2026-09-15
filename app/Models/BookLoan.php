<?php

namespace App\Models;

use Database\Factories\BookLoanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookLoan extends Model
{
    /** @use HasFactory<BookLoanFactory> */
    use HasFactory;
}
