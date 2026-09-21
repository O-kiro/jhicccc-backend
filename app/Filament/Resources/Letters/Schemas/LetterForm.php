<?php

namespace App\Filament\Resources\Letters\Schemas;

use App\Models\Letter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LetterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('direction')->label('Jenis')->options(Letter::ARAH)->required()->live(),
            TextInput::make('number')
                ->label('Nomor Surat')
                ->required()
                ->maxLength(100)
                // Unik per jenis: surat masuk dari instansi lain boleh saja
                // bernomor sama dengan surat keluar madrasah.
                ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, callable $get) => $rule->where('direction', $get('direction'))),
            TextInput::make('subject')->label('Perihal')->required()->maxLength(255)->columnSpanFull(),
            TextInput::make('correspondent')
                ->label(fn (callable $get): string => $get('direction') === 'keluar' ? 'Tujuan' : 'Pengirim')
                ->required()
                ->maxLength(255),
            DatePicker::make('dated_on')->label('Tanggal Surat')->required(),
            DatePicker::make('recorded_on')->label('Tanggal Dicatat')->required()->default(now()),
            TextInput::make('file_url')
                ->label('Tautan Berkas')
                ->helperText('Tautan hasil pindaian di Google Drive atau penyimpanan lain.')
                ->url()
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('note')->label('Disposisi / Catatan')->rows(3)->columnSpanFull(),
        ]);
    }
}
