<?php

namespace App\Filament\Resources\AlumniAccounts;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\AlumniAccounts\Pages\CreateAlumniAccount;
use App\Filament\Resources\AlumniAccounts\Pages\EditAlumniAccount;
use App\Filament\Resources\AlumniAccounts\Pages\ListAlumniAccounts;
use App\Filament\Resources\AlumniAccounts\Schemas\AlumniAccountForm;
use App\Filament\Resources\AlumniAccounts\Tables\AlumniAccountsTable;
use App\Models\AlumniAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AlumniAccountResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = AlumniAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $modelLabel = 'Akun Alumni';

    protected static ?string $pluralModelLabel = 'Akun Alumni';

    protected static string|\UnitEnum|null $navigationGroup = 'Alumni';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AlumniAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlumniAccountsTable::configure($table);
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
            'index' => ListAlumniAccounts::route('/'),
            'create' => CreateAlumniAccount::route('/create'),
            'edit' => EditAlumniAccount::route('/{record}/edit'),
        ];
    }
}
