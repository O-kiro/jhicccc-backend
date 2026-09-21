<?php

namespace App\Filament\Resources\IntegrityDocuments;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\IntegrityDocuments\Pages\CreateIntegrityDocument;
use App\Filament\Resources\IntegrityDocuments\Pages\EditIntegrityDocument;
use App\Filament\Resources\IntegrityDocuments\Pages\ListIntegrityDocuments;
use App\Filament\Resources\IntegrityDocuments\Schemas\IntegrityDocumentForm;
use App\Filament\Resources\IntegrityDocuments\Tables\IntegrityDocumentsTable;
use App\Models\IntegrityDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IntegrityDocumentResource extends Resource
{
    use DibatasiPeran;

    /** Modulnya sendiri, bukan grup navigasinya — lihat App\Support\Peran. */
    public static function modulAkses(): string
    {
        return 'Kelola ZI';
    }

    protected static ?string $model = IntegrityDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $modelLabel = 'Dokumen ZI';

    protected static ?string $pluralModelLabel = 'Kelola ZI';

    protected static string|\UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return IntegrityDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IntegrityDocumentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIntegrityDocuments::route('/'),
            'create' => CreateIntegrityDocument::route('/create'),
            'edit' => EditIntegrityDocument::route('/{record}/edit'),
        ];
    }
}
