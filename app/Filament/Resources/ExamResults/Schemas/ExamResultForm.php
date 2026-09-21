<?php

namespace App\Filament\Resources\ExamResults\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExamResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Siswa')
                    ->relationship('student', 'name')
                    ->disabled()
                    ->dehydrated(false),

                Select::make('exam_id')
                    ->label('Ujian')
                    ->relationship('exam', 'title')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('score')
                    ->label('Nilai')
                    ->helperText('Diisi otomatis dari jawaban siswa. Ubah hanya bila ada koreksi manual.')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),

                DateTimePicker::make('finished_at')
                    ->label('Dikumpulkan Pada')
                    ->seconds(false),
            ]);
    }
}
