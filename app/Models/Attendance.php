<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'date', 'code', 'check_in_at', 'source', 'note'])]
class Attendance extends Model
{
    use HasFactory;

    /** Kode buku induk beserta artinya. */
    public const KODE = [
        'H' => 'Hadir',
        'T' => 'Terlambat',
        'S' => 'Sakit',
        'I' => 'Izin',
        'A' => 'Alpha',
        'L' => 'Libur',
    ];

    public const SUMBER = [
        'fingerprint' => 'Mesin Sidik Jari',
        'wali_kelas' => 'Wali Kelas',
        'wali_murid' => 'Wali Murid',
        'manual' => 'Manual',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
