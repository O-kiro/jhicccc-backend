<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Menyetel ulang kata sandi sebuah akun portal.
 *
 * Madrasah belum punya server surel, jadi tautan "lupa kata sandi" lewat
 * email belum bisa dipakai. Gantinya: admin menerbitkan sandi sementara yang
 * ditampilkan sekali di layar, lalu menyerahkannya langsung ke yang
 * bersangkutan. Seluruh sesi lama dicabut supaya perangkat yang terlanjur
 * masuk dengan sandi lama ikut keluar.
 */
class SetelUlangSandi
{
    public static function make(string $sebutan = 'akun'): Action
    {
        return Action::make('setel_ulang_sandi')
            ->label('Setel Ulang Sandi')
            ->icon('heroicon-o-key')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Setel ulang kata sandi')
            ->modalDescription(
                'Kata sandi baru dibuat acak dan ditampilkan sekali saja. '
                ."Catat lalu serahkan ke {$sebutan} yang bersangkutan — "
                .'semua perangkat yang sedang masuk akan dikeluarkan.'
            )
            ->modalSubmitActionLabel('Terbitkan Sandi Baru')
            ->action(function (Model $record): void {
                $sandi = self::acak();

                $record->forceFill(['password' => $sandi])->save();
                $record->tokens()->delete();

                Notification::make()
                    ->title('Kata sandi baru: '.$sandi)
                    ->body('Catat sekarang — sandi ini tidak bisa dilihat lagi setelah pesan ini ditutup.')
                    ->success()
                    // Persisten: sandinya harus sempat disalin, bukan hilang
                    // sendiri setelah beberapa detik.
                    ->persistent()
                    ->send();
            });
    }

    /** Sandi acak yang masih mudah dibacakan lewat telepon. */
    private static function acak(): string
    {
        return Str::password(10, letters: true, numbers: true, symbols: false, spaces: false);
    }
}
