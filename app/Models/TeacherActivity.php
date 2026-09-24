<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanGambarUnggahan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu kegiatan harian guru di luar jam mengajar kelas. */
#[Fillable(['teacher_id', 'classroom_id', 'date', 'activity', 'photo_path'])]
class TeacherActivity extends Model
{
    use HasFactory, MembersihkanGambarUnggahan;

    /**
     * Kolom berkas yang ikut dihapus saat barisnya diganti atau dihapus.
     *
     * @return list<string>
     */
    protected function kolomBerkasUnggahan(): array
    {
        return ['photo_path'];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /** Alamat publik bukti foto; null bila tidak ada. */
    public function photoUrl(): ?string
    {
        return $this->photo_path ? '/storage/'.$this->photo_path : null;
    }
}
