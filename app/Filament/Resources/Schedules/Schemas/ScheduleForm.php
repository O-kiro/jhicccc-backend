<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('classroom_id')
                    ->label('Kelas')
                    ->relationship('classroom', 'name')
                    ->required(),
                Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name')
                    ->required(),
                Select::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'name')
                    ->required(),
                TextInput::make('day_of_week')
                    ->label('Hari')
                    ->required()
                    ->numeric(),
                TimePicker::make('starts_at')
                    ->label('Mulai')
                    ->required(),
                TimePicker::make('ends_at')
                    ->label('Selesai')
                    ->required(),
                TextInput::make('meeting_url')
                    ->label('Tautan Kelas')
                    ->url(),
            ]);
    }
}
