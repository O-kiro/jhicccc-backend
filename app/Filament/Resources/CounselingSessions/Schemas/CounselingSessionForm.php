<?php

namespace App\Filament\Resources\CounselingSessions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CounselingSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')->label('Siswa')->relationship('student', 'name')->searchable()->preload()->required(),
            Select::make('teacher_id')->label('Guru BK')->relationship('teacher', 'name')->searchable()->preload(),
            DatePicker::make('held_on')->label('Tanggal')->required()->default(now())->maxDate(now()),
            Select::make('category')
                ->label('Bidang')
                ->options([
                    'Akademik' => 'Akademik',
                    'Pribadi' => 'Pribadi',
                    'Sosial' => 'Sosial',
                    'Karier' => 'Karier',
                ])
                ->required(),
            Textarea::make('summary')->label('Ringkasan Sesi')->required()->rows(4)->columnSpanFull(),
            Textarea::make('follow_up')->label('Tindak Lanjut')->rows(3)->columnSpanFull(),
            Toggle::make('is_confidential')
                ->label('Rahasia')
                ->helperText('Penanda saja. Panel belum membatasi siapa yang bisa membukanya — semua admin tetap bisa melihat catatan ini.'),
        ]);
    }
}
