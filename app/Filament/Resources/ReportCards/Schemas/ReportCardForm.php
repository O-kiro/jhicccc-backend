<?php

namespace App\Filament\Resources\ReportCards\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReportCardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Siswa')
                    ->relationship('student', 'name')
                    ->required(),
                TextInput::make('academic_year')
                    ->label('Tahun Pelajaran')
                    ->required(),
                TextInput::make('semester')
                    ->label('Semester')
                    ->required(),
                TextInput::make('average_score')
                    ->label('Rata-Rata')
                    ->required()
                    ->numeric(),
                TextInput::make('class_rank')
                    ->label('Peringkat')
                    ->numeric(),
                TextInput::make('class_size')
                    ->label('Jumlah Siswa')
                    ->numeric(),
                TextInput::make('attendance_percentage')
                    ->label('Kehadiran (%)')
                    ->required()
                    ->numeric(),
            ]);
    }
}
