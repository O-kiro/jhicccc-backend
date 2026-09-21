<?php

namespace App\Filament\Resources\DisciplineRules;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\DisciplineRules\Pages\CreateDisciplineRule;
use App\Filament\Resources\DisciplineRules\Pages\EditDisciplineRule;
use App\Filament\Resources\DisciplineRules\Pages\ListDisciplineRules;
use App\Filament\Resources\DisciplineRules\Schemas\DisciplineRuleForm;
use App\Filament\Resources\DisciplineRules\Tables\DisciplineRulesTable;
use App\Models\DisciplineRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DisciplineRuleResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = DisciplineRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmarkSquare;

    protected static ?string $modelLabel = 'Aturan Tata Tertib';

    protected static ?string $pluralModelLabel = 'Buku Tatib';

    protected static string|\UnitEnum|null $navigationGroup = 'Kesiswaan';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return DisciplineRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DisciplineRulesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDisciplineRules::route('/'),
            'create' => CreateDisciplineRule::route('/create'),
            'edit' => EditDisciplineRule::route('/{record}/edit'),
        ];
    }
}
