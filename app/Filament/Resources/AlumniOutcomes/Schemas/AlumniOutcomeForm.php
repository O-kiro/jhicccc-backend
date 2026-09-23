<?php

namespace App\Filament\Resources\AlumniOutcomes\Schemas;

use App\Models\AlumniOutcome;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class AlumniOutcomeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('year')
                    ->label('Tahun Kelulusan')
                    ->required()
                    ->numeric()
                    ->minValue(1980)
                    ->maxValue((int) now()->year + 1)
                    ->default((int) now()->year)
                    ->live(onBlur: true),

                Select::make('category')
                    ->label('Kategori')
                    ->options(collect(AlumniOutcome::KATEGORI)->map(fn (array $k): string => $k['label'])->all())
                    ->required()
                    // Satu kategori hanya boleh sekali per tahun; tanpa aturan
                    // ini penyimpanan kedua menabrak indeks unik dan balas 500.
                    ->rule(fn (Get $get, ?AlumniOutcome $record) => Rule::unique('alumni_outcomes', 'category')
                        ->where('year', (int) $get('year'))
                        ->ignore($record?->getKey()))
                    ->validationMessages([
                        'unique' => 'Kategori ini sudah diisi untuk tahun tersebut.',
                    ]),

                TextInput::make('students')
                    ->label('Jumlah Siswa')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                TextInput::make('note')
                    ->label('Jalur Populer')
                    ->helperText('Mis. "SNBP, SNBT, & Seleksi Mandiri PTN".')
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('sort')
                    ->label('Urutan Tampil')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
