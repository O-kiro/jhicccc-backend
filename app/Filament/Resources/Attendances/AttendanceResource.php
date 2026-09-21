<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\Attendances\Pages\CreateAttendance;
use App\Filament\Resources\Attendances\Pages\EditAttendance;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Filament\Resources\Attendances\Schemas\AttendanceForm;
use App\Filament\Resources\Attendances\Tables\AttendancesTable;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?string $modelLabel = 'Kehadiran';

    protected static ?string $pluralModelLabel = 'Kehadiran Harian';

    protected static string|\UnitEnum|null $navigationGroup = 'Absensi';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return AttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendancesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendances::route('/'),
            'create' => CreateAttendance::route('/create'),
            'edit' => EditAttendance::route('/{record}/edit'),
        ];
    }
}
