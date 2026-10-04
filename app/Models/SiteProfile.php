<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanGambarUnggahan;
use App\Models\Concerns\MemicuRevalidasiSitus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'principal_name', 'principal_role', 'principal_message',
    'principal_photo', 'principal_photo_path',
    'building_photo', 'building_photo_path',
    'whatsapp',
])]
class SiteProfile extends Model
{
    use HasFactory;
    use MembersihkanGambarUnggahan;
    use MemicuRevalidasiSitus;

    /** Dua kolom unggahan, bukan `image_path` bawaan trait. */
    protected function kolomBerkasUnggahan(): array
    {
        return ['principal_photo_path', 'building_photo_path'];
    }

    /**
     * Satu-satunya baris, dibuat bila belum ada.
     *
     * Dipakai panel maupun API supaya keduanya tidak pernah menemui null dan
     * tidak perlu saling tahu siapa yang membuat barisnya lebih dulu.
     */
    public static function ambil(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function publicPrincipalPhoto(): ?string
    {
        return $this->principal_photo_path
            ? '/storage/'.ltrim($this->principal_photo_path, '/')
            : $this->principal_photo;
    }

    public function publicBuildingPhoto(): ?string
    {
        return $this->building_photo_path
            ? '/storage/'.ltrim($this->building_photo_path, '/')
            : $this->building_photo;
    }
}
