<?php

namespace App\Filament\Resources\AlumniForumCategories;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\AlumniForumCategories\Pages\CreateAlumniForumCategory;
use App\Filament\Resources\AlumniForumCategories\Pages\EditAlumniForumCategory;
use App\Filament\Resources\AlumniForumCategories\Pages\ListAlumniForumCategories;
use App\Filament\Resources\AlumniForumCategories\Schemas\AlumniForumCategoryForm;
use App\Filament\Resources\AlumniForumCategories\Tables\AlumniForumCategoriesTable;
use App\Models\AlumniForumCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AlumniForumCategoryResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = AlumniForumCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $modelLabel = 'Kategori Forum Alumni';

    protected static ?string $pluralModelLabel = 'Kategori Forum Alumni';

    protected static string|\UnitEnum|null $navigationGroup = 'Alumni';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return AlumniForumCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlumniForumCategoriesTable::configure($table);
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
            'index' => ListAlumniForumCategories::route('/'),
            'create' => CreateAlumniForumCategory::route('/create'),
            'edit' => EditAlumniForumCategory::route('/{record}/edit'),
        ];
    }
}
