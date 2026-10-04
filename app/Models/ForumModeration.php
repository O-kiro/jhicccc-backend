<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Catatan moderasi otomatis: postingan yang ditolak Gemini, atau yang tayang
 * tanpa sempat dicek karena Gemini sedang bermasalah.
 */
#[Fillable(['forum', 'jenis', 'status', 'penulis_type', 'penulis_id', 'payload', 'alasan', 'postingan_type', 'postingan_id'])]
class ForumModeration extends Model
{
    public const DITOLAK = 'ditolak';

    public const BELUM_DICEK = 'belum_dicek';

    public const DIPULIHKAN = 'dipulihkan';

    public const AMAN = 'aman';

    public const DIHAPUS = 'dihapus';

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    /** Kelas postingan sesuai forum dan jenisnya. */
    public function kelasPostingan(): string
    {
        return match ("{$this->forum}.{$this->jenis}") {
            'siswa.topik' => ForumThread::class,
            'siswa.balasan' => ForumReply::class,
            'alumni.topik' => AlumniForumThread::class,
            'alumni.balasan' => AlumniForumReply::class,
        };
    }

    /**
     * Menayangkan postingan yang ditolak Gemini, untuk kasus salah tangkap.
     * Gagal (false) bila topik tempat balasannya sudah tidak ada.
     */
    public function pulihkan(): bool
    {
        $kelas = $this->kelasPostingan();
        $payload = $this->payload;

        $kolomTopik = ['siswa' => 'forum_thread_id', 'alumni' => 'alumni_forum_thread_id'][$this->forum];
        $kelasTopik = ['siswa' => ForumThread::class, 'alumni' => AlumniForumThread::class][$this->forum];

        if ($this->jenis === 'balasan') {
            if (! $kelasTopik::query()->whereKey($payload[$kolomTopik] ?? null)->exists()) {
                return false;
            }
            // Induknya mungkin sudah dihapus sejak itu; tempel ke topik saja.
            if (! empty($payload['parent_id']) && ! $kelas::query()->whereKey($payload['parent_id'])->exists()) {
                $payload['parent_id'] = null;
            }
        }

        $postingan = $kelas::query()->create($payload);

        $this->update([
            'status' => self::DIPULIHKAN,
            'postingan_type' => $postingan->getMorphClass(),
            'postingan_id' => $postingan->getKey(),
        ]);

        return true;
    }

    public function penulis(): MorphTo
    {
        return $this->morphTo();
    }

    public function postingan(): MorphTo
    {
        return $this->morphTo();
    }
}
