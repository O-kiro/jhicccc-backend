<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['year', 'category', 'students', 'note', 'sort'])]
class AlumniOutcome extends Model
{
    use HasFactory;

    /** Kategori penelusuran beserta label dan warnanya di diagram. */
    public const KATEGORI = [
        'ptn' => ['label' => 'Lolos PTN', 'tone' => 'teal'],
        'pts' => ['label' => 'PTS / Swasta', 'tone' => 'blue'],
        'kedinasan' => ['label' => 'Sekolah Kedinasan', 'tone' => 'gold'],
        'kerja' => ['label' => 'Kerja / Wirausaha', 'tone' => 'muted'],
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['year' => 'integer', 'students' => 'integer'];
    }

    public function label(): string
    {
        return self::KATEGORI[$this->category]['label'] ?? $this->category;
    }

    public function tone(): string
    {
        return self::KATEGORI[$this->category]['tone'] ?? 'teal';
    }
}
