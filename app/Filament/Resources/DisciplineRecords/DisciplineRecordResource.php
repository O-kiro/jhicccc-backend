<?php

namespace App\Filament\Resources\DisciplineRecords;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\DisciplineRecords\Pages\CreateDisciplineRecord;
use App\Filament\Resources\DisciplineRecords\Pages\EditDisciplineRecord;
use App\Filament\Resources\DisciplineRecords\Pages\ListDisciplineRecords;
use App\Filament\Resources\DisciplineRecords\Schemas\DisciplineRecordForm;
use App\Filament\Resources\DisciplineRecords\Tables\DisciplineRecordsTable;
use App\Models\DisciplineRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DisciplineRecordResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = DisciplineRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $modelLabel = 'Catatan Kedisiplinan';

    protected static ?string $pluralModelLabel = 'Catatan Kedisiplinan';

    protected static string|\UnitEnum|null $navigationGroup = 'Kesiswaan';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return DisciplineRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DisciplineRecordsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDisciplineRecords::route('/'),
            'create' => CreateDisciplineRecord::route('/create'),
            'edit' => EditDisciplineRecord::route('/{record}/edit'),
        ];
    }
}
