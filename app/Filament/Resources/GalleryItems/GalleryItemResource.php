<?php

namespace App\Filament\Resources\GalleryItems;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\GalleryItems\Pages\CreateGalleryItem;
use App\Filament\Resources\GalleryItems\Pages\EditGalleryItem;
use App\Filament\Resources\GalleryItems\Pages\ListGalleryItems;
use App\Filament\Resources\GalleryItems\Schemas\GalleryItemForm;
use App\Filament\Resources\GalleryItems\Tables\GalleryItemsTable;
use App\Models\GalleryItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GalleryItemResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = GalleryItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $modelLabel = 'Foto';

    protected static ?string $pluralModelLabel = 'Galeri';

    protected static string|\UnitEnum|null $navigationGroup = 'My Website';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return GalleryItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GalleryItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryItems::route('/'),
            'create' => CreateGalleryItem::route('/create'),
            'edit' => EditGalleryItem::route('/{record}/edit'),
        ];
    }
}
