<?php

namespace App\Filament\Resources\TeachingJournals\Schemas;

use App\Models\Schedule;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TeachingJournalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('schedule_id')
                    ->label('Jadwal')
                    ->options(fn (): array => Schedule::query()
                        ->with(['subject', 'classroom'])
                        ->get()
                        ->mapWithKeys(fn (Schedule $s): array => [
                            $s->id => sprintf(
                                '%s — %s (%s)',
                                $s->classroom?->name ?? '?',
                                $s->subject?->name ?? '?',
                                substr((string) $s->starts_at, 0, 5),
                            ),
                        ])
                        ->all())
                    ->searchable()
                    ->required(),

                DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->default(now()),

                Select::make('teacher_id')
                    ->label('Guru Pengajar')
                    ->relationship('teacher', 'name')
                    ->helperText('Isi dengan guru inval bila yang mengajar bukan guru jadwalnya.')
                    ->searchable()
                    ->preload(),

                TextInput::make('present_count')
                    ->label('Jumlah Hadir')
                    ->numeric()
                    ->minValue(0),

                TextInput::make('topic')
                    ->label('Materi yang Diajarkan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('note')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
