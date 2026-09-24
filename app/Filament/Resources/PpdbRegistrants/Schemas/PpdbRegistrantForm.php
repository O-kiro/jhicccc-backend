<?php

namespace App\Filament\Resources\PpdbRegistrants\Schemas;

use App\Models\PpdbRegistrant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PpdbRegistrantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Pendaftar')
                    ->columns(2)
                    ->schema([
                        TextInput::make('registration_number')
                            ->label('Nomor Pendaftaran')
                            ->helperText('Dipakai calon siswa untuk masuk. Contoh: PPDB26-0001.')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            // Nomor baru diusulkan otomatis supaya panitia tidak
                            // perlu mengingat urutan terakhir.
                            ->default(fn (): string => self::nomorBerikutnya()),
                        Select::make('jalur')
                            ->label('Jalur')
                            ->options(array_combine(PpdbRegistrant::JALUR, PpdbRegistrant::JALUR))
                            ->default(PpdbRegistrant::JALUR[0])
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('origin_school')
                            ->label('Asal Sekolah')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Nomor HP Orang Tua')
                            ->tel()
                            ->maxLength(30),
                    ]),

                Section::make('Akses Unggah Berkas')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->revealable()
                            ->minLength(6)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'Sampaikan ke calon siswa bersama nomor pendaftarannya.'
                                : 'Kosongkan bila tidak ingin mengganti kata sandi.'),
                        Toggle::make('is_active')
                            ->label('Akun Aktif')
                            ->helperText('Menonaktifkan akun ikut mengeluarkan pendaftar dari perangkatnya.')
                            ->default(true)
                            ->inline(false),
                        Textarea::make('note')
                            ->label('Catatan Panitia')
                            ->rows(2)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /** "PPDB26-0007" dari nomor terakhir tahun ini. */
    private static function nomorBerikutnya(): string
    {
        $awalan = 'PPDB'.now()->format('y').'-';

        $terakhir = PpdbRegistrant::query()
            ->where('registration_number', 'like', $awalan.'%')
            ->orderByDesc('registration_number')
            ->value('registration_number');

        $urut = $terakhir ? (int) Str::afterLast($terakhir, '-') : 0;

        return $awalan.str_pad((string) ($urut + 1), 4, '0', STR_PAD_LEFT);
    }
}
