<?php

namespace App\Filament\Resources\AlumniForumThreads;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\AlumniForumThreads\Pages\CreateAlumniForumThread;
use App\Filament\Resources\AlumniForumThreads\Pages\EditAlumniForumThread;
use App\Filament\Resources\AlumniForumThreads\Pages\ListAlumniForumThreads;
use App\Filament\Resources\AlumniForumThreads\Schemas\AlumniForumThreadForm;
use App\Filament\Resources\AlumniForumThreads\Tables\AlumniForumThreadsTable;
use App\Models\AlumniForumThread;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AlumniForumThreadResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = AlumniForumThread::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $modelLabel = 'Topik Forum Alumni';

    protected static ?string $pluralModelLabel = 'Topik Forum Alumni';

    protected static string|\UnitEnum|null $navigationGroup = 'Alumni';

    protected static ?int $navigationSort = 5;

    /** Topik hanya lahir dari portal alumni; panel untuk memoderasi. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AlumniForumThreadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlumniForumThreadsTable::configure($table);
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
            'index' => ListAlumniForumThreads::route('/'),
            'create' => CreateAlumniForumThread::route('/create'),
            'edit' => EditAlumniForumThread::route('/{record}/edit'),
        ];
    }
}
