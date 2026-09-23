<?php

namespace App\Filament\Resources\Scholarships\Schemas;

use App\Models\Scholarship;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScholarshipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Beasiswa')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('category')
                            ->label('Kategori')
                            ->helperText('Dipakai sebagai penyaring di portal. Samakan penulisannya antarprogram.')
                            ->required()
                            ->datalist(fn (): array => Scholarship::query()
                                ->distinct()->orderBy('category')->pluck('category')->all())
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options(Scholarship::STATUS)
                            ->default('dibuka')
                            ->required(),
                        TextInput::make('quota')
                            ->label('Kuota (kursi)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        DatePicker::make('deadline')
                            ->label('Batas Akhir Pendaftaran'),
                        Textarea::make('benefits')
                            ->label('Fasilitas & Insentif')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        TextInput::make('target')
                            ->label('Sasaran')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('url')
                            ->label('Tautan Pendaftaran')
                            ->url()
                            ->helperText('Tombol "Lihat Selengkapnya" di portal mengarah ke sini.')
                            ->maxLength(2048)
                            ->columnSpanFull(),
                    ]),

                Section::make('Rekap Pendaftaran')
                    ->description('Angka ringkasan di portal — program aktif, kuota, dan penyerapan — dihitung dari sini.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('applicants')
                            ->label('Pendaftar Masuk')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('verified')
                            ->label('Lolos Verifikasi')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('sort')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
