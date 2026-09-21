<?php

namespace App\Filament\Resources\Exams\RelationManagers;

use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Soal';

    protected static ?string $modelLabel = 'Soal';

    /** Huruf opsi yang diizinkan; sama dengan kolom key selebar 2 karakter. */
    private const KUNCI = ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E'];

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('number')
                ->label('Nomor Soal')
                ->helperText('Menentukan urutan dan penomoran navigator di layar siswa.')
                ->required()
                ->numeric()
                ->minValue(1),

            Select::make('type')
                ->label('Jenis')
                ->options(['Pilihan Ganda' => 'Pilihan Ganda'])
                ->default('Pilihan Ganda')
                ->required(),

            Textarea::make('body')
                ->label('Teks Soal')
                ->required()
                ->rows(4)
                ->columnSpanFull(),

            Repeater::make('options')
                ->label('Pilihan Jawaban')
                ->relationship()
                ->schema([
                    Select::make('key')
                        ->label('Huruf')
                        ->options(self::KUNCI)
                        ->required()
                        // Dua opsi berhuruf sama melanggar batasan unik di
                        // basis data; ditahan di formulir supaya tidak jadi
                        // galat SQL mentah.
                        ->distinct(),

                    TextInput::make('body')
                        ->label('Isi Pilihan')
                        ->required()
                        ->maxLength(500)
                        ->columnSpan(2),

                    Toggle::make('is_correct')
                        ->label('Kunci')
                        ->inline(false),
                ])
                // ExamQuestionOption menandai is_correct sebagai #[Hidden],
                // dan Repeater mengisi formulirnya lewat attributesToArray()
                // yang menghormati penanda itu. Tanpa pemulihan ini, kunci
                // jawaban tampil "mati" saat soal dibuka — lalu benar-benar
                // terhapus begitu guru menekan simpan.
                ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                    $data['is_correct'] = (bool) ExamQuestionOption::query()
                        ->whereKey($data['id'] ?? null)
                        ->value('is_correct');

                    return $data;
                })
                ->columns(4)
                ->defaultItems(5)
                ->minItems(2)
                ->maxItems(5)
                ->addActionLabel('Tambah pilihan')
                ->helperText('Tandai tepat satu pilihan sebagai kunci jawaban. Kunci tidak pernah dikirim ke portal siswa.')
                ->rule(static function (): Closure {
                    return static function (string $attribute, mixed $value, Closure $fail): void {
                        $benar = collect($value)->filter(fn (array $opsi): bool => (bool) ($opsi['is_correct'] ?? false));

                        if ($benar->count() !== 1) {
                            $fail('Tepat satu pilihan harus ditandai sebagai kunci jawaban.');
                        }
                    };
                })
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('number')
                    ->label('No.')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('body')
                    ->label('Soal')
                    ->wrap()
                    ->limit(120)
                    ->searchable(),
                TextColumn::make('options_count')
                    ->label('Pilihan')
                    ->counts('options')
                    ->badge(),
                TextColumn::make('kunci')
                    ->label('Kunci')
                    ->badge()
                    ->color('success')
                    // Hanya tampil di panel admin. Resource API sengaja tidak
                    // pernah memuat is_correct.
                    ->state(fn (ExamQuestion $record): string => $record->options
                        ->firstWhere('is_correct', true)?->key ?? '—'),
            ])
            ->defaultSort('number')
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
