<?php

namespace App\Filament\Resources\ForumCategories;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\ForumCategories\Pages\CreateForumCategory;
use App\Filament\Resources\ForumCategories\Pages\EditForumCategory;
use App\Filament\Resources\ForumCategories\Pages\ListForumCategories;
use App\Filament\Resources\ForumCategories\Schemas\ForumCategoryForm;
use App\Filament\Resources\ForumCategories\Tables\ForumCategoriesTable;
use App\Models\ForumCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ForumCategoryResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = ForumCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $modelLabel = 'Kategori Forum';

    protected static ?string $pluralModelLabel = 'Kategori Forum';

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ForumCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ForumCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForumCategories::route('/'),
            'create' => CreateForumCategory::route('/create'),
            'edit' => EditForumCategory::route('/{record}/edit'),
        ];
    }
}
