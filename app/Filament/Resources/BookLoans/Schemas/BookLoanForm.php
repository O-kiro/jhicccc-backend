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

                // Peminjam: siswa ATAU guru, tepat salah satu. Aturannya
                // dipasang di kolom siswa saja supaya pesannya muncul sekali.
                Select::make('student_id')
                    ->label('Peminjam — Siswa')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->requiredWithout('teacher_id')
                    ->prohibits('teacher_id')
                    ->validationMessages([
                        'required_without' => 'Pilih peminjamnya: siswa atau guru.',
                        'prohibits' => 'Pilih salah satu saja: siswa atau guru.',
                    ])
                    ->helperText('Kosongkan bila peminjamnya guru.'),

                Select::make('teacher_id')
                    ->label('Peminjam — Guru')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Kosongkan bila peminjamnya siswa.'),

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
