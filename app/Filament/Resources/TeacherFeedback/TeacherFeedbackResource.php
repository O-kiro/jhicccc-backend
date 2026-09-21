<?php

namespace App\Filament\Resources\TeacherFeedback;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\TeacherFeedback\Pages\CreateTeacherFeedback;
use App\Filament\Resources\TeacherFeedback\Pages\EditTeacherFeedback;
use App\Filament\Resources\TeacherFeedback\Pages\ListTeacherFeedback;
use App\Filament\Resources\TeacherFeedback\Schemas\TeacherFeedbackForm;
use App\Filament\Resources\TeacherFeedback\Tables\TeacherFeedbackTable;
use App\Models\TeacherFeedback;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TeacherFeedbackResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = TeacherFeedback::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $modelLabel = 'Catatan Guru';

    protected static ?string $pluralModelLabel = 'Catatan Guru';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return TeacherFeedbackForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeacherFeedbackTable::configure($table);
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
            'index' => ListTeacherFeedback::route('/'),
            'create' => CreateTeacherFeedback::route('/create'),
            'edit' => EditTeacherFeedback::route('/{record}/edit'),
        ];
    }
}
