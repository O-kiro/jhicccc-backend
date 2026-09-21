<?php

namespace App\Filament\Resources\StudentPermits\Schemas;

use App\Models\StudentPermit;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentPermitForm
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

                Select::make('kind')
                    ->label('Jenis Izin')
                    ->options(StudentPermit::JENIS)
                    ->default('keluar_kelas')
                    ->required(),

                DateTimePicker::make('left_at')
                    ->label('Jam Keluar')
                    ->seconds(false)
                    ->default(now())
                    ->required(),

                DateTimePicker::make('returned_at')
                    ->label('Jam Kembali')
                    ->helperText('Kosongkan selama siswanya belum kembali.')
                    ->seconds(false)
                    ->after('left_at'),

                Select::make('teacher_id')
                    ->label('Guru Piket')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload(),

                TextInput::make('reason')
                    ->label('Keperluan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
