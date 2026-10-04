<?php

namespace App\Filament\Resources\ModerasiForum;

use App\Filament\Concerns\DibatasiPeran;
use App\Filament\Resources\ModerasiForum\Pages\ListForumModerations;
use App\Models\ForumModeration;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Log moderasi otomatis Gemini. Hanya baca + tiga tindakan: memulihkan yang
 * salah tolak, menandai aman, atau menghapus yang lolos tanpa sempat dicek.
 */
class ForumModerationResource extends Resource
{
    use DibatasiPeran;

    protected static ?string $model = ForumModeration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?string $modelLabel = 'Moderasi Forum';

    protected static ?string $pluralModelLabel = 'Moderasi Forum';

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $n = ForumModeration::query()->where('status', ForumModeration::BELUM_DICEK)->count();

        return $n > 0 ? (string) $n : null;
    }

    public static function table(Table $table): Table
    {
        $label = [
            ForumModeration::DITOLAK => 'Ditolak',
            ForumModeration::BELUM_DICEK => 'Belum dicek',
            ForumModeration::DIPULIHKAN => 'Dipulihkan',
            ForumModeration::AMAN => 'Aman',
            ForumModeration::DIHAPUS => 'Dihapus',
        ];

        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $label[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        ForumModeration::DITOLAK, ForumModeration::DIHAPUS => 'danger',
                        ForumModeration::BELUM_DICEK => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('forum')
                    ->formatStateUsing(fn (ForumModeration $r) => ucfirst($r->forum).' · '.$r->jenis)
                    ->label('Forum'),
                TextColumn::make('penulis.name')
                    ->label('Penulis'),
                TextColumn::make('payload.body')
                    ->label('Isi')
                    ->description(fn (ForumModeration $r) => $r->payload['title'] ?? null, position: 'above')
                    ->limit(160)
                    ->wrap(),
                TextColumn::make('alasan')
                    ->label('Alasan')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('j M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options($label),
                SelectFilter::make('forum')->options(['siswa' => 'Siswa', 'alumni' => 'Alumni']),
            ])
            ->recordActions([
                Action::make('pulihkan')
                    ->label('Pulihkan')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('success')
                    ->visible(fn (ForumModeration $r) => $r->status === ForumModeration::DITOLAK)
                    ->requiresConfirmation()
                    ->modalDescription('Postingan ini akan ditayangkan di forum seperti aslinya.')
                    ->action(function (ForumModeration $r) {
                        $r->pulihkan()
                            ? Notification::make()->title('Postingan ditayangkan')->success()->send()
                            : Notification::make()->title('Topiknya sudah dihapus; balasan tidak bisa dipulihkan')->danger()->send();
                    }),
                Action::make('aman')
                    ->label('Tandai aman')
                    ->icon(Heroicon::OutlinedCheck)
                    ->visible(fn (ForumModeration $r) => $r->status === ForumModeration::BELUM_DICEK)
                    ->action(fn (ForumModeration $r) => $r->update(['status' => ForumModeration::AMAN])),
                Action::make('hapus')
                    ->label('Hapus postingan')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->visible(fn (ForumModeration $r) => in_array($r->status, [ForumModeration::BELUM_DICEK, ForumModeration::DIPULIHKAN], true))
                    ->requiresConfirmation()
                    ->action(function (ForumModeration $r) {
                        $r->postingan?->delete();
                        $r->update(['status' => ForumModeration::DIHAPUS]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForumModerations::route('/'),
        ];
    }
}
