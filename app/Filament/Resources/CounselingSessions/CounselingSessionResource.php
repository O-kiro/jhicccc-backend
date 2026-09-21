<?php

namespace App\Filament\Resources\CounselingSessions;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\CounselingSessions\Pages\CreateCounselingSession;
use App\Filament\Resources\CounselingSessions\Pages\EditCounselingSession;
use App\Filament\Resources\CounselingSessions\Pages\ListCounselingSessions;
use App\Filament\Resources\CounselingSessions\Schemas\CounselingSessionForm;
use App\Filament\Resources\CounselingSessions\Tables\CounselingSessionsTable;
use App\Models\CounselingSession;
use App\Support\Peran;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CounselingSessionResource extends Resource
{
    use DibatasiPeran;

    /** Modulnya sendiri, bukan grup navigasinya — lihat App\Support\Peran. */
    public static function modulAkses(): string
    {
        return 'Konseling';
    }

    protected static ?string $model = CounselingSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $modelLabel = 'Sesi Konseling';

    protected static ?string $pluralModelLabel = 'Konseling';

    protected static string|\UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?int $navigationSort = 2;

    /**
     * Catatan bertanda rahasia hanya dimuat untuk Guru BK — tidak disaring di
     * tampilan, tapi di kueri, sehingga tidak ada jalan membukanya lewat
     * daftar, pencarian, maupun URL sunting.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return Peran::bolehBacaKonselingRahasia(auth()->user())
            ? $query
            : $query->where('is_confidential', false);
    }

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
