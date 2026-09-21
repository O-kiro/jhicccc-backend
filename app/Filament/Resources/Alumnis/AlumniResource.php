<?php

namespace App\Filament\Resources\Alumnis;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\Alumnis\Pages\CreateAlumni;
use App\Filament\Resources\Alumnis\Pages\EditAlumni;
use App\Filament\Resources\Alumnis\Pages\ListAlumnis;
use App\Filament\Resources\Alumnis\Schemas\AlumniForm;
use App\Filament\Resources\Alumnis\Tables\AlumnisTable;
use App\Models\Alumni;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AlumniResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = Alumni::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $modelLabel = 'Alumni';

    protected static ?string $pluralModelLabel = 'Alumni';

    protected static string|\UnitEnum|null $navigationGroup = 'My Website';

    protected static ?int $navigationSort = 12;

    /** Tanpa ini Filament memakai /admin/alumnis. */
    protected static ?string $slug = 'alumni';

    public static function form(Schema $schema): Schema
    {
        return AlumniForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlumnisTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAlumnis::route('/'),
            'create' => CreateAlumni::route('/create'),
            'edit' => EditAlumni::route('/{record}/edit'),
        ];
    }
}
