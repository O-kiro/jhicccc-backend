<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanGambarUnggahan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Berkas RDM yang diunggah guru untuk satu kelas. */
#[Fillable([
    'teacher_id', 'classroom_id', 'academic_year', 'semester',
    'file_path', 'original_name', 'size_kb', 'note',
])]
class ReportUpload extends Model
{
    use HasFactory, MembersihkanGambarUnggahan;

    /**
     * @return list<string>
     */
    protected function kolomBerkasUnggahan(): array
    {
        return ['file_path'];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['size_kb' => 'integer'];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function fileUrl(): string
    {
        return '/storage/'.$this->file_path;
    }
}
