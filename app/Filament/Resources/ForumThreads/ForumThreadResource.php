<?php

namespace App\Filament\Resources\ForumThreads;

use App\Filament\Resources\ForumThreads\Pages\EditForumThread;
use App\Filament\Resources\ForumThreads\Pages\ListForumThreads;
use App\Filament\Resources\ForumThreads\RelationManagers\RepliesRelationManager;
use App\Filament\Resources\ForumThreads\Schemas\ForumThreadForm;
use App\Filament\Resources\ForumThreads\Tables\ForumThreadsTable;
use App\Models\ForumThread;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ForumThreadResource extends Resource
{
    protected static ?string $model = ForumThread::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $modelLabel = 'Topik Forum';

    protected static ?string $pluralModelLabel = 'Topik Forum';

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 4;

    /**
     * Topik dibuat siswa dari portal. Halaman ini untuk moderasi —
     * menyunting isi yang tidak pantas atau menghapusnya — bukan untuk
     * membuat topik atas nama siswa.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ForumThreadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ForumThreadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RepliesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForumThreads::route('/'),
            'edit' => EditForumThread::route('/{record}/edit'),
        ];
    }
}
