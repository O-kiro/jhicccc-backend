<?php

namespace App\Filament\Resources\CounselingSessions;

use App\Filament\Resources\CounselingSessions\Pages\CreateCounselingSession;
use App\Filament\Resources\CounselingSessions\Pages\EditCounselingSession;
use App\Filament\Resources\CounselingSessions\Pages\ListCounselingSessions;
use App\Filament\Resources\CounselingSessions\Schemas\CounselingSessionForm;
use App\Filament\Resources\CounselingSessions\Tables\CounselingSessionsTable;
use App\Models\CounselingSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CounselingSessionResource extends Resource
{
    protected static ?string $model = CounselingSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $modelLabel = 'Sesi Konseling';

    protected static ?string $pluralModelLabel = 'Konseling';

    protected static string|\UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CounselingSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CounselingSessionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCounselingSessions::route('/'),
            'create' => CreateCounselingSession::route('/create'),
            'edit' => EditCounselingSession::route('/{record}/edit'),
        ];
    }
}
