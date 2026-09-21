<?php

namespace App\Filament\Pages\Modules;

use App\Models\Student;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Klasemen poin kedisiplinan.
 *
 * Angkanya dijumlahkan langsung dari discipline_records, bukan disimpan di
 * kolom tersendiri: menghapus atau mengoreksi satu catatan harus langsung
 * terlihat di peringkat, tanpa perlu menghitung ulang apa pun.
 */
class Kesiswaan extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Kesiswaan';

    protected static ?string $navigationLabel = 'Klasemen Poin';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Klasemen Poin Kedisiplinan';

    protected string $view = 'filament.pages.modules.klasemen';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->where('is_active', true)
                    ->withSum('disciplineRecords as poin', 'points')
                    ->withCount('disciplineRecords as kasus')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable(),
                TextColumn::make('classroom.name')
                    ->label('Kelas')
                    ->badge(),
                TextColumn::make('kasus')
                    ->label('Jumlah Catatan')
                    ->badge(),
                TextColumn::make('poin')
                    ->label('Total Poin')
                    ->badge()
                    // Siswa tanpa catatan sama sekali bernilai null, bukan 0.
                    ->formatStateUsing(fn (?int $state): string => (string) ($state ?? 0))
                    ->color(fn (?int $state): string => match (true) {
                        ($state ?? 0) <= 0 => 'success',
                        $state < 50 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
            ])
            ->defaultSort('poin', 'desc')
            ->filters([
                SelectFilter::make('classroom')
                    ->label('Kelas')
                    ->relationship('classroom', 'name'),
            ])
            ->recordActions([
                Action::make('catatan')
                    ->label('Lihat Catatan')
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn (Student $record): string => route(
                        'filament.admin.resources.discipline-records.index',
                        ['tableSearch' => $record->name],
                    )),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('classroom'));
    }
}
