<?php

namespace App\Filament\Resources\Assessments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssessmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Siswa')
                    ->relationship('student', 'name')
                    ->required(),
                Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name')
                    ->required(),
                TextInput::make('title')
                    ->label('Judul'),
                TextInput::make('score')
                    ->label('Nilai')
                    ->required()
                    ->numeric(),
                DatePicker::make('assessed_on')
                    ->label('Tanggal Penilaian')
                    ->required(),
            ]);
    }
}
