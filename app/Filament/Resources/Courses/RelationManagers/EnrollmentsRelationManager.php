<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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

            TextInput::make('progress_percentage')
                ->label('Progres (%)')
                ->helperText('Diisi manual. Belum dihitung otomatis dari modul yang selesai.')
                ->required()
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->default(0),
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
                TextColumn::make('progress_percentage')
                    ->label('Progres')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 75 => 'success',
                        $state >= 40 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
            ])
            ->defaultSort('progress_percentage', 'desc')
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
