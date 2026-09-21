<?php

namespace App\Filament\Resources\BookLoans;

use App\Filament\Resources\BookLoans\Pages\CreateBookLoan;
use App\Filament\Resources\BookLoans\Pages\EditBookLoan;
use App\Filament\Resources\BookLoans\Pages\ListBookLoans;
use App\Filament\Resources\BookLoans\Schemas\BookLoanForm;
use App\Filament\Resources\BookLoans\Tables\BookLoansTable;
use App\Models\BookLoan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BookLoanResource extends Resource
{
    protected static ?string $model = BookLoan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $modelLabel = 'Pinjaman';

    protected static ?string $pluralModelLabel = 'Pinjaman Buku';

    protected static string|\UnitEnum|null $navigationGroup = 'E-Library';

    protected static ?int $navigationSort = 2;

    /** Jumlah pinjaman yang belum dikembalikan, tampil di samping menu. */
    public static function getNavigationBadge(): ?string
    {
        $aktif = static::getModel()::query()->whereNull('returned_at')->count();

        return $aktif > 0 ? (string) $aktif : null;
    }

    public static function form(Schema $schema): Schema
    {
        return BookLoanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookLoansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookLoans::route('/'),
            'create' => CreateBookLoan::route('/create'),
            'edit' => EditBookLoan::route('/{record}/edit'),
        ];
    }
}
