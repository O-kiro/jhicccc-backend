<?php

namespace App\Filament\Resources\Exams\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Ujian')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('classroom_id')
                    ->label('Kelas')
                    ->relationship('classroom', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                DateTimePicker::make('starts_at')
                    ->label('Mulai')
                    ->helperText('Sesi CBT hanya bisa dibuka di antara waktu mulai dan selesai.')
                    ->seconds(false)
                    ->required(),

                DateTimePicker::make('ends_at')
                    ->label('Selesai')
                    ->seconds(false)
                    ->required()
                    ->after('starts_at'),

                Select::make('priority')
                    ->label('Prioritas')
                    ->helperText('Memunculkan lencana "Prioritas" di portal siswa. Kosongkan untuk ujian biasa.')
                    ->options(['Tinggi' => 'Tinggi', 'Sedang' => 'Sedang'])
                    ->placeholder('Tanpa prioritas'),
            ]);
    }
}
