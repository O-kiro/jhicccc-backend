<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanGambarUnggahan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu berkas unggahan pendaftar PPDB. */
#[Fillable([
    'ppdb_registrant_id', 'jenis', 'file_path', 'original_name',
    'size_kb', 'status', 'note', 'verified_at', 'verified_by',
])]
class PpdbDocument extends Model
{
    use HasFactory, MembersihkanGambarUnggahan;

    public const STATUS = [
        'menunggu' => 'Menunggu Verifikasi',
        'diterima' => 'Diterima',
        'ditolak' => 'Perlu Diganti',
    ];

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
        return ['verified_at' => 'datetime', 'size_kb' => 'integer'];
    }

    public function registrant(): BelongsTo
    {
        return $this->belongsTo(PpdbRegistrant::class, 'ppdb_registrant_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function labelJenis(): string
    {
        return PpdbRegistrant::BERKAS[$this->jenis]['label'] ?? $this->jenis;
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function fileUrl(): string
    {
        return '/storage/'.$this->file_path;
    }
}
