<?php

namespace App\Filament\Resources\GuestVisits\Schemas;

use App\Models\GuestVisit;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GuestVisitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('registration_code')
                    ->label('Kode Registrasi')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(30)
                    // Kode dibuatkan saat menambah supaya petugas tidak perlu
                    // mengarang format sendiri.
                    ->default(fn (): string => 'BT-'.Str::upper(Str::random(6))),

                Select::make('service_id')
                    ->label('Jenis Kunjungan')
                    ->relationship('service', 'name', fn ($query) => $query->where('kind', 'kunjungan'))
                    ->searchable()
                    ->preload(),

                TextInput::make('guest_name')
                    ->label('Nama Tamu')
                    ->required()
                    ->maxLength(255),

                TextInput::make('institution')
                    ->label('Instansi')
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->tel()
                    ->maxLength(30),

                Select::make('status')
                    ->label('Status')
                    ->options(GuestVisit::STATUS)
                    ->default('pending')
                    ->required(),

                DateTimePicker::make('arrived_at')
                    ->label('Jam Datang')
                    ->seconds(false)
                    ->default(now())
                    ->required(),

                DateTimePicker::make('finished_at')
                    ->label('Jam Selesai')
                    ->seconds(false)
                    ->after('arrived_at'),

                Select::make('rating')
                    ->label('Penilaian Layanan')
                    ->helperText('Diisi tamu setelah dilayani. Boleh dikosongkan.')
                    ->options([1 => '1 — Sangat kurang', 2 => '2 — Kurang', 3 => '3 — Cukup', 4 => '4 — Baik', 5 => '5 — Sangat baik']),

                Textarea::make('purpose')
                    ->label('Keperluan')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
