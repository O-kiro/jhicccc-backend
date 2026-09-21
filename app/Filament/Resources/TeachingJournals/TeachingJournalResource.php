<?php

namespace App\Filament\Resources\TeachingJournals;

use App\Filament\Resources\TeachingJournals\Pages\CreateTeachingJournal;
use App\Filament\Resources\TeachingJournals\Pages\EditTeachingJournal;
use App\Filament\Resources\TeachingJournals\Pages\ListTeachingJournals;
use App\Filament\Resources\TeachingJournals\Schemas\TeachingJournalForm;
use App\Filament\Resources\TeachingJournals\Tables\TeachingJournalsTable;
use App\Models\TeachingJournal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TeachingJournalResource extends Resource
{
    protected static ?string $model = TeachingJournal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?string $modelLabel = 'Jurnal KBM';

    protected static ?string $pluralModelLabel = 'Jurnal KBM';

    protected static string|\UnitEnum|null $navigationGroup = 'Absensi';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return TeachingJournalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeachingJournalsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeachingJournals::route('/'),
            'create' => CreateTeachingJournal::route('/create'),
            'edit' => EditTeachingJournal::route('/{record}/edit'),
        ];
    }
}
