<?php

namespace App\Filament\Resources\StudentPermits;

use App\Filament\Resources\StudentPermits\Pages\CreateStudentPermit;
use App\Filament\Resources\StudentPermits\Pages\EditStudentPermit;
use App\Filament\Resources\StudentPermits\Pages\ListStudentPermits;
use App\Filament\Resources\StudentPermits\Schemas\StudentPermitForm;
use App\Filament\Resources\StudentPermits\Tables\StudentPermitsTable;
use App\Models\StudentPermit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentPermitResource extends Resource
{
    protected static ?string $model = StudentPermit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowRightOnRectangle;

    protected static ?string $modelLabel = 'Izin Keluar/Masuk';

    protected static ?string $pluralModelLabel = 'Guru Piket';

    protected static string|\UnitEnum|null $navigationGroup = 'Absensi';

    protected static ?int $navigationSort = 3;

    /** Berapa siswa yang tercatat keluar dan belum kembali. */
    public static function getNavigationBadge(): ?string
    {
        $diLuar = static::getModel()::query()->whereNull('returned_at')->count();

        return $diLuar > 0 ? (string) $diLuar : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return StudentPermitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentPermitsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentPermits::route('/'),
            'create' => CreateStudentPermit::route('/create'),
            'edit' => EditStudentPermit::route('/{record}/edit'),
        ];
    }
}
