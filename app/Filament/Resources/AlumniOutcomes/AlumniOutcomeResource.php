<?php

namespace App\Filament\Resources\AlumniOutcomes;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\AlumniOutcomes\Pages\CreateAlumniOutcome;
use App\Filament\Resources\AlumniOutcomes\Pages\EditAlumniOutcome;
use App\Filament\Resources\AlumniOutcomes\Pages\ListAlumniOutcomes;
use App\Filament\Resources\AlumniOutcomes\Schemas\AlumniOutcomeForm;
use App\Filament\Resources\AlumniOutcomes\Tables\AlumniOutcomesTable;
use App\Models\AlumniOutcome;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AlumniOutcomeResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = AlumniOutcome::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?string $modelLabel = 'Sebaran Kelulusan';

    protected static ?string $pluralModelLabel = 'Sebaran Kelulusan';

    protected static string|\UnitEnum|null $navigationGroup = 'Alumni';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return AlumniOutcomeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlumniOutcomesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAlumniOutcomes::route('/'),
            'create' => CreateAlumniOutcome::route('/create'),
            'edit' => EditAlumniOutcome::route('/{record}/edit'),
        ];
    }
}
