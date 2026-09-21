<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('teacher_id')
                    ->label('Pengajar')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload(),

                Select::make('classroom_id')
                    ->label('Kelas')
                    ->relationship('classroom', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('academic_year')
                    ->label('Tahun Pelajaran')
                    ->placeholder('2025/2026')
                    ->required()
                    ->maxLength(20),

                Select::make('semester')
                    ->label('Semester')
                    ->options(['Ganjil' => 'Ganjil', 'Genap' => 'Genap'])
                    ->required(),
            ]);
    }
}
