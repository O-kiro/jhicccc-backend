<?php

namespace App\Filament\Resources\AgendaItems;

use App\Filament\Resources\AgendaItems\Pages\CreateAgendaItem;
use App\Filament\Resources\AgendaItems\Pages\EditAgendaItem;
use App\Filament\Resources\AgendaItems\Pages\ListAgendaItems;
use App\Filament\Resources\AgendaItems\Schemas\AgendaItemForm;
use App\Filament\Resources\AgendaItems\Tables\AgendaItemsTable;
use App\Models\AgendaItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AgendaItemResource extends Resource
{
    protected static ?string $model = AgendaItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $modelLabel = 'Agenda';

    protected static ?string $pluralModelLabel = 'Agenda';

    protected static string|\UnitEnum|null $navigationGroup = 'My Website';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return AgendaItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgendaItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgendaItems::route('/'),
            'create' => CreateAgendaItem::route('/create'),
            'edit' => EditAgendaItem::route('/{record}/edit'),
        ];
    }
}
