<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Memberi tahu situs Next.js bahwa konten My Website berubah.
 *
 * Panggilannya dijadwalkan SETELAH respons terkirim (terminating), bukan saat
 * menyimpan: admin tidak ikut menunggu situs, dan kalau situs sedang mati
 * penyimpanan tetap berhasil — situs akan menyusul sendiri dalam 60 detik.
 *
 * Beberapa perubahan dalam satu permintaan (menyeret urutan sepuluh baris
 * sekaligus) cukup memicu satu panggilan.
 *
 * Callback terminating didaftarkan SEKALI di AppServiceProvider, bukan di
 * setiap minta(): callback terminating tidak pernah dilepas, jadi mendaftar
 * ulang tiap kali membuatnya menumpuk di proses yang hidup lama dan
 * menjalankan panggilan lama berulang-ulang.
 */
class RevalidasiSitus
{
    private bool $diminta = false;

    /** Dipanggil model konten; pengiriman sebenarnya menunggu akhir permintaan. */
    public function minta(): void
    {
        $this->diminta = true;
    }

    /** Dijalankan sekali di akhir tiap permintaan (lihat AppServiceProvider). */
    public function kirimBilaDiminta(): void
    {
        if (! $this->diminta) {
            return;
        }

        $this->diminta = false;
        $this->kirim();
    }

    /** Mengirim panggilan sekarang juga. Tidak pernah melempar galat. */
    public function kirim(): bool
    {
        if (! $this->aktif()) {
            return false;
        }

        try {
            $res = Http::timeout(3)
                ->withHeaders(['x-revalidate-secret' => config('services.situs.revalidate_secret')])
                ->acceptJson()
                ->post(config('services.situs.revalidate_url'));

            if ($res->successful()) {
                return true;
            }

            Log::warning('Revalidasi situs ditolak', ['status' => $res->status()]);
        } catch (Throwable $e) {
            Log::warning('Situs tidak bisa dihubungi untuk revalidasi', ['galat' => $e->getMessage()]);
        }

        return false;
    }

    private function aktif(): bool
    {
        return filled(config('services.situs.revalidate_url'))
            && filled(config('services.situs.revalidate_secret'));
    }
}
