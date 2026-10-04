<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\DibatasiPeran;
use App\Models\SiteProfile;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Profil madrasah dan sambutan kepala madrasah.
 *
 * Halaman, bukan Resource: isinya satu baris pengaturan, bukan koleksi. Daftar
 * dan tombol "tambah" milik Resource tidak masuk akal di sini — admin hanya
 * perlu satu formulir yang bisa disimpan berulang.
 *
 * Dua bagian ini sebelumnya ditanam di lib/content.ts frontend, sehingga
 * fotonya mustahil diganti tanpa mengubah kode dan menerbitkan ulang situs.
 */
class ProfilSitus extends Page
{
    use DibatasiPeran;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'Profil & Sambutan';

    protected static ?string $title = 'Profil Madrasah & Sambutan Kepala';

    protected static string|\UnitEnum|null $navigationGroup = 'My Website';

    protected static ?int $navigationSort = 14;

    protected string $view = 'filament.pages.profil-situs';

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteProfile::ambil()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([

                Section::make('Sambutan Kepala Madrasah')
                    ->description('Tampil di beranda, pada seksi "Sambutan Kepala Madrasah".')
                    ->columns(2)
                    ->schema([
                        TextInput::make('principal_name')
                            ->label('Nama')
                            ->placeholder('Drs. H. Farhadi, M.Si')
                            ->maxLength(120),
                        TextInput::make('principal_role')
                            ->label('Jabatan')
                            ->placeholder('Kepala MAN Kota Batu')
                            ->maxLength(120),
                        Textarea::make('principal_message')
                            ->label('Teks Sambutan')
                            ->rows(8)
                            ->columnSpanFull(),
                        FileUpload::make('principal_photo_path')
                            ->label('Unggah Foto Kepala Madrasah')
                            ->helperText('Foto formal, sebaiknya potret. JPG, PNG, atau WebP, maksimal 2 MB.')
                            ->image()
                            ->disk('public')
                            ->directory('situs/profil')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->columnSpanFull(),
                        TextInput::make('principal_photo')
                            ->label('atau Jalur Foto yang Sudah Ada')
                            ->helperText('Mis. /photos/kepala-madrasah.jpg. Diabaikan bila ada unggahan.')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('WhatsApp Admin')
                    ->description('Tombol WhatsApp di pojok kanan bawah situs. Kosongkan untuk menyembunyikannya.')
                    ->schema([
                        TextInput::make('whatsapp')
                            ->label('Nomor WhatsApp')
                            ->placeholder('0812-3456-7890')
                            ->helperText('Boleh diawali 0, 62, atau +62; spasi dan tanda hubung diabaikan.')
                            ->tel()
                            ->regex('/^\\+?[0-9\\s\\-()]{9,20}$/')
                            ->maxLength(25),
                    ]),

                Section::make('Foto Gedung Madrasah')
                    ->description('Tampil di beranda, pada seksi "Tentang MAKOBA".')
                    ->schema([
                        FileUpload::make('building_photo_path')
                            ->label('Unggah Foto Gedung')
                            ->helperText('Foto lanskap paling cocok. JPG, PNG, atau WebP, maksimal 2 MB.')
                            ->image()
                            ->disk('public')
                            ->directory('situs/profil')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048),
                        TextInput::make('building_photo')
                            ->label('atau Jalur Foto yang Sudah Ada')
                            ->helperText('Mis. /photos/gedung-madrasah.jpg. Diabaikan bila ada unggahan.')
                            ->maxLength(255),
                    ]),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('simpan')
                ->label('Simpan')
                ->submit('simpan'),
        ];
    }

    public function simpan(): void
    {
        SiteProfile::ambil()->update($this->form->getState());

        Notification::make()
            ->title('Profil situs disimpan')
            ->body('Perubahan tampil di situs publik dalam beberapa detik.')
            ->success()
            ->send();
    }
}
