<?php

namespace App\Filament\Resources\Exams;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\Exams\Pages\CreateExam;
use App\Filament\Resources\Exams\Pages\EditExam;
use App\Filament\Resources\Exams\Pages\ListExams;
use App\Filament\Resources\Exams\RelationManagers\QuestionsRelationManager;
use App\Filament\Resources\Exams\Schemas\ExamForm;
use App\Filament\Resources\Exams\Tables\ExamsTable;
use App\Models\Exam;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExamResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = Exam::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $modelLabel = 'Ujian';

    protected static ?string $pluralModelLabel = 'Ujian';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return ExamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExams::route('/'),
            'create' => CreateExam::route('/create'),
            'edit' => EditExam::route('/{record}/edit'),
        ];
    }
}
