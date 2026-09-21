<?php

namespace App\Filament\Resources\Teachers;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\Teachers\Pages\CreateTeacher;
use App\Filament\Resources\Teachers\Pages\EditTeacher;
use App\Filament\Resources\Teachers\Pages\ListTeachers;
use App\Filament\Resources\Teachers\Schemas\TeacherForm;
use App\Filament\Resources\Teachers\Tables\TeachersTable;
use App\Models\Teacher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TeacherResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = Teacher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $modelLabel = 'Guru';

    protected static ?string $pluralModelLabel = 'Guru';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return TeacherForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeachersTable::configure($table);
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
            'index' => ListTeachers::route('/'),
            'create' => CreateTeacher::route('/create'),
            'edit' => EditTeacher::route('/{record}/edit'),
        ];
    }
}
