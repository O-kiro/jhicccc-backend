<?php

namespace App\Filament\Resources\Books\Tables;

use App\Http\Resources\V1\BookResource as BookApiResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author')
                    ->label('Penulis')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('total_pages')
                    ->label('Halaman')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('url')
                    ->label('Ada Berkas')
                    ->boolean()
                    ->tooltip('Tombol "Lanjutkan Membaca" hanya muncul bila tautannya diisi.'),
                TextColumn::make('loans_count')
                    ->label('Dipinjam')
                    ->counts('loans')
                    ->badge(),
            ])
            ->defaultSort('title')
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(array_combine(
                        array_keys(BookApiResource::TONES),
                        array_keys(BookApiResource::TONES),
                    )),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
