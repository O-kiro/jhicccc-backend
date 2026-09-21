<?php

namespace App\Filament\Resources\DisciplineRecords\Schemas;

use App\Models\DisciplineRule;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DisciplineRecordForm
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

                Select::make('discipline_rule_id')
                    ->label('Aturan')
                    ->relationship('rule', 'title', fn ($query) => $query->where('is_active', true))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    // Poin ikut terisi dari aturan, tapi tetap bisa disesuaikan
                    // dan disimpan per kejadian — lihat komentar migrasinya.
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $aturan = DisciplineRule::find($state);
                        $set('points', $aturan?->signedPoints() ?? 0);
                    }),

                TextInput::make('points')
                    ->label('Poin')
                    ->helperText('Terisi otomatis dari aturan. Nilai negatif berarti penghargaan.')
                    ->required()
                    ->numeric(),

                DatePicker::make('occurred_on')
                    ->label('Tanggal Kejadian')
                    ->required()
                    ->default(now())
                    ->maxDate(now()),

                Select::make('teacher_id')
                    ->label('Dicatat Oleh')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload(),

                Textarea::make('note')
                    ->label('Keterangan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
