<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\Enrollment;
use App\Models\ModuleCompletion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Peserta';

    protected static ?string $modelLabel = 'Peserta';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')
                ->label('Siswa')
                ->relationship('student', 'name')
                ->searchable()
                ->preload()
                ->required(),

        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student.name')
            ->columns([
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.nisn')
                    ->label('NISN')
                    ->searchable(),
                TextColumn::make('progres')
                    ->label('Progres')
                    // Dihitung dari modul yang ditandai selesai oleh siswa ini,
                    // bukan diketik — lihat migrasi derive_course_progress_from_modules.
                    ->state(function (Enrollment $record): int {
                        $total = $this->getOwnerRecord()->modules()->count();

                        if ($total === 0) {
                            return 0;
                        }

                        $selesai = ModuleCompletion::query()
                            ->where('student_id', $record->student_id)
                            ->whereIn('course_module_id', $this->getOwnerRecord()->modules()->select('id'))
                            ->count();

                        return (int) round($selesai / $total * 100);
                    })
                    ->suffix('%')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 75 => 'success',
                        $state >= 40 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->defaultSort('id')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
