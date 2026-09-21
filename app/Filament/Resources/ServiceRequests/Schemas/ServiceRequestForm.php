<?php

namespace App\Filament\Resources\ServiceRequests\Schemas;

use App\Models\ServiceRequest;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ticket')
                    ->label('Nomor Tiket')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(30)
                    ->default(fn (): string => 'PTSP-'.Str::upper(Str::random(6))),

                Select::make('service_id')
                    ->label('Jenis Layanan')
                    ->relationship('service', 'name', fn ($query) => $query->where('kind', 'layanan'))
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('applicant_name')
                    ->label('Nama Pemohon')
                    ->required()
                    ->maxLength(255),

                TextInput::make('contact')
                    ->label('Kontak')
                    ->maxLength(120),

                Select::make('status')
                    ->label('Status')
                    ->options(ServiceRequest::STATUS)
                    ->default('baru')
                    ->required(),

                DateTimePicker::make('submitted_at')
                    ->label('Diajukan')
                    ->seconds(false)
                    ->default(now())
                    ->required(),

                DateTimePicker::make('completed_at')
                    ->label('Selesai')
                    ->helperText('Terisi otomatis saat permohonan ditandai selesai.')
                    ->seconds(false),

                Textarea::make('note')
                    ->label('Keterangan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
