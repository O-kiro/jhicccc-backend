<?php

namespace App\Filament\Resources\PaymentTypes;

use App\Filament\Resources\PaymentTypes\Pages\CreatePaymentType;
use App\Filament\Resources\PaymentTypes\Pages\EditPaymentType;
use App\Filament\Resources\PaymentTypes\Pages\ListPaymentTypes;
use App\Filament\Resources\PaymentTypes\Schemas\PaymentTypeForm;
use App\Filament\Resources\PaymentTypes\Tables\PaymentTypesTable;
use App\Models\PaymentType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentTypeResource extends Resource
{
    protected static ?string $model = PaymentType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $modelLabel = 'Jenis Pembayaran';

    protected static ?string $pluralModelLabel = 'Master Pembayaran';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PaymentTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentTypes::route('/'),
            'create' => CreatePaymentType::route('/create'),
            'edit' => EditPaymentType::route('/{record}/edit'),
        ];
    }
}
