<?php

namespace App\Filament\Resources\Classrooms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClassroomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('level')
                    ->label('Tingkat')
                    ->required(),
                TextInput::make('academic_year')
                    ->label('Tahun Pelajaran')
                    ->required(),
                Select::make('homeroom_teacher_id')
                    ->label('Wali Kelas')
                    ->relationship('homeroomTeacher', 'name'),
            ]);
    }
}
