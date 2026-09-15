<?php

namespace App\Filament\Pages\Modules;

use Filament\Pages\Page;

/**
 * Halaman rangka untuk modul MAKOBADIG yang strukturnya sudah disepakati
 * (lihat dashboard-admin-tour.md) tetapi datanya belum dibangun.
 *
 * Tujuannya agar bentuk sidebar lengkap sejak awal dan tiap menu menjelaskan
 * sendiri apa yang akan diisi — bukan halaman kosong tanpa keterangan.
 * Turunannya cukup menetapkan judul, grup, ikon, dan daftar fitur.
 */
abstract class ModulePage extends Page
{
    protected string $view = 'filament.pages.modules.placeholder';

    /**
     * Ringkasan satu kalimat tentang kegunaan modul ini.
     */
    abstract public static function getModuleSummary(): string;

    /**
     * Fitur yang direncanakan, disalin dari dokumen tour.
     *
     * @return array<int, array{title: string, description: string}>
     */
    abstract public static function getPlannedFeatures(): array;

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'summary' => static::getModuleSummary(),
            'features' => static::getPlannedFeatures(),
        ];
    }
}
