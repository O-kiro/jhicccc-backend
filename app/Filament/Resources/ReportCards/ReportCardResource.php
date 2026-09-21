<?php

namespace App\Filament\Resources\ReportCards;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\ReportCards\Pages\CreateReportCard;
use App\Filament\Resources\ReportCards\Pages\EditReportCard;
use App\Filament\Resources\ReportCards\Pages\ListReportCards;
use App\Filament\Resources\ReportCards\Schemas\ReportCardForm;
use App\Filament\Resources\ReportCards\Tables\ReportCardsTable;
use App\Models\ReportCard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReportCardResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = ReportCard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $modelLabel = 'Rapor';

    protected static ?string $pluralModelLabel = 'Rapor Digital';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ReportCardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportCardsTable::configure($table);
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
            'index' => ListReportCards::route('/'),
            'create' => CreateReportCard::route('/create'),
            'edit' => EditReportCard::route('/{record}/edit'),
        ];
    }
}
