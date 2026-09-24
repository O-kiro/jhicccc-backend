<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Menghapus berkas unggahan yang sudah tidak dirujuk: saat diganti berkas
 * lain, dikosongkan, atau barisnya dihapus.
 *
 * Tanpa ini, setiap penggantian berkas meninggalkan sampah di
 * storage/app/public yang tidak pernah terpakai lagi.
 *
 * Bawaannya mengurus kolom `image_path`; model dengan nama kolom lain
 * menimpa kolomBerkasUnggahan().
 */
trait MembersihkanGambarUnggahan
{
    /**
     * @return list<string>
     */
    protected function kolomBerkasUnggahan(): array
    {
        return ['image_path'];
    }

    protected static function bootMembersihkanGambarUnggahan(): void
    {
        static::updated(function (Model $model): void {
            foreach ($model->kolomBerkasUnggahan() as $kolom) {
                $lama = $model->getOriginal($kolom);

                if ($lama && $model->wasChanged($kolom)) {
                    Storage::disk('public')->delete($lama);
                }
            }
        });

        static::deleted(function (Model $model): void {
            foreach ($model->kolomBerkasUnggahan() as $kolom) {
                if ($model->{$kolom}) {
                    Storage::disk('public')->delete($model->{$kolom});
                }
            }
        });
    }
}
