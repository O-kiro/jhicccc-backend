<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Menghapus berkas unggahan (kolom image_path) yang sudah tidak dirujuk:
 * saat diganti berkas lain, dikosongkan, atau barisnya dihapus.
 *
 * Tanpa ini, setiap penggantian foto meninggalkan berkas lama di
 * storage/app/public yang tidak pernah terpakai lagi.
 */
trait MembersihkanGambarUnggahan
{
    protected static function bootMembersihkanGambarUnggahan(): void
    {
        static::updated(function (Model $model): void {
            $lama = $model->getOriginal('image_path');

            if ($lama && $model->wasChanged('image_path')) {
                Storage::disk('public')->delete($lama);
            }
        });

        static::deleted(function (Model $model): void {
            if ($model->image_path) {
                Storage::disk('public')->delete($model->image_path);
            }
        });
    }
}
