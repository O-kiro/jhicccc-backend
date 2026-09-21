<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Models\Attendance;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Siswa')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->default(now()),

                Select::make('code')
                    ->label('Kode Kehadiran')
                    ->options(Attendance::KODE)
                    ->default('H')
                    ->required(),

                TimePicker::make('check_in_at')
                    ->label('Jam Masuk')
                    ->seconds(false),

                Select::make('source')
                    ->label('Sumber')
                    ->options(Attendance::SUMBER)
                    ->default('manual')
                    ->required(),

                TextInput::make('note')
                    ->label('Keterangan')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
