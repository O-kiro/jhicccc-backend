<?php

namespace App\Filament\Resources\PaymentTypes\Schemas;

use App\Models\PaymentType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Kode')->required()->unique(ignoreRecord: true)->maxLength(30),
            TextInput::make('name')->label('Nama')->required()->maxLength(255),
            TextInput::make('amount')
                ->label('Nominal')
                ->prefix('Rp')
                ->required()
                ->numeric()
                ->minValue(0),
            Select::make('period')->label('Periode')->options(PaymentType::PERIODE)->default('bulanan')->required(),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }
}
