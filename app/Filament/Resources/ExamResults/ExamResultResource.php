<?php

namespace App\Filament\Resources\ExamResults;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\ExamResults\Pages\EditExamResult;
use App\Filament\Resources\ExamResults\Pages\ListExamResults;
use App\Filament\Resources\ExamResults\Schemas\ExamResultForm;
use App\Filament\Resources\ExamResults\Tables\ExamResultsTable;
use App\Models\ExamResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExamResultResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = ExamResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static ?string $modelLabel = 'Hasil Ujian';

    protected static ?string $pluralModelLabel = 'Hasil Ujian';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 7;

    /**
     * Hasil ujian lahir saat siswa menekan "Selesai Ujian" dan dinilai server.
     * Membuatnya manual dari sini akan menghasilkan nilai tanpa jawaban —
     * karena itu hanya bisa dikoreksi atau dihapus.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ExamResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamResultsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamResults::route('/'),
            'edit' => EditExamResult::route('/{record}/edit'),
        ];
    }
}
