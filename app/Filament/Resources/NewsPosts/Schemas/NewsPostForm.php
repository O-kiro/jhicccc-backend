<?php

namespace App\Filament\Resources\NewsPosts\Schemas;

use App\Filament\Support\PilihanSitus;
use App\Models\NewsPost;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NewsPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('title')
                ->label('Judul')
                ->required()
                ->maxLength(160)
                ->columnSpanFull()
                ->live(onBlur: true)
                // Slug hanya ikut terisi saat membuat: mengubahnya belakangan
                // memutus tautan yang sudah dibagikan.
                ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create'
                    ? $set('slug', Str::slug((string) $state))
                    : null),
            TextInput::make('slug')->label('Slug')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(160),
            TextInput::make('category')
                ->label('Kategori')
                ->required()
                ->maxLength(40)
                ->datalist(fn (): array => NewsPost::query()->distinct()->orderBy('category')->pluck('category')->all()),
            DatePicker::make('published_on')
                ->label('Tanggal Terbit')
                ->helperText('Berita bertanggal masa depan baru tampil pada tanggalnya. Yang terbaru menjadi berita utama.')
                ->required()
                ->default(now()),
            TextInput::make('author')->label('Penulis')->required()->default('Humas MAKOBA')->maxLength(80),
            PilihanSitus::warna(),
            TextInput::make('image')
                ->label('Gambar')
                ->helperText('Jalur foto di situs (mis. /photos/berita.jpg) atau alamat lengkap.')
                ->maxLength(255),
            Textarea::make('excerpt')->label('Ringkasan')->required()->rows(2)->maxLength(300)->columnSpanFull(),
            Repeater::make('content')
                ->label('Isi Berita')
                ->simple(Textarea::make('paragraf')->rows(4)->required())
                ->minItems(1)
                ->addActionLabel('Tambah paragraf')
                ->columnSpanFull(),
            Toggle::make('is_published')->label('Terbit')->helperText('Matikan untuk menyimpan sebagai draf.')->default(true),
        ]);
    }
}
