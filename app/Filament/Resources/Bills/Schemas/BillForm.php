<?php

namespace App\Filament\Resources\Bills\Schemas;

use App\Models\PaymentType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')
                ->label('Siswa')
                ->relationship('student', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('payment_type_id')
                ->label('Jenis Pembayaran')
                ->relationship('paymentType', 'name', fn ($query) => $query->where('is_active', true))
                ->searchable()
                ->preload()
                ->required()
                ->live()
                // Nominal disalin dari master saat tagihan dibuat; lihat
                // komentar migrasi bills.
                ->afterStateUpdated(function ($state, callable $set): void {
                    $set('amount', PaymentType::find($state)?->amount ?? 0);
                }),

            TextInput::make('period')
                ->label('Periode')
                ->placeholder('2026-09')
                ->helperText('Bebas formatnya, tapi konsisten — dipakai mencegah tagihan ganda.')
                ->required()
                ->maxLength(30),

            TextInput::make('amount')->label('Nominal Tagihan')->prefix('Rp')->required()->numeric()->minValue(0),

            TextInput::make('amount_paid')
                ->label('Sudah Dibayar')
                ->prefix('Rp')
                ->required()
                ->numeric()
                ->minValue(0)
                ->default(0),

            DatePicker::make('due_on')->label('Jatuh Tempo'),

            DateTimePicker::make('paid_at')->label('Waktu Pelunasan')->seconds(false),

            TextInput::make('receipt_no')->label('Nomor Kuitansi')->maxLength(50),
        ]);
    }
}
