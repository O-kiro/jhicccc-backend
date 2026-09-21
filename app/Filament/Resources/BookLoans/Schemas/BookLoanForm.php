<?php

namespace App\Filament\Resources\BookLoans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookLoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('book_id')
                    ->label('Buku')
                    ->relationship('book', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('student_id')
                    ->label('Siswa')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                DatePicker::make('due_on')
                    ->label('Jatuh Tempo')
                    ->required()
                    ->default(now()->addDays(14)),

                TextInput::make('current_page')
                    ->label('Halaman Terakhir Dibaca')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                DateTimePicker::make('returned_at')
                    ->label('Dikembalikan Pada')
                    ->helperText('Kosongkan selama buku masih dipinjam. Diisi berarti pinjaman selesai.'),
            ]);
    }
}
