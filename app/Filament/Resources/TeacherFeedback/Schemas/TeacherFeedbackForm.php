<?php

namespace App\Filament\Resources\TeacherFeedback\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TeacherFeedbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Siswa')
                    ->relationship('student', 'name')
                    ->required(),
                Select::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'name')
                    ->required(),
                TextInput::make('role')
                    ->label('Peran')
                    ->required(),
                Textarea::make('body')
                    ->label('Isi')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
