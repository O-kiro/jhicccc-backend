<?php

namespace App\Filament\Widgets;

use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * Dasbor admin dengan susunan yang sama persis dengan halaman Overview
 * portal siswa: panel sapaan bergradasi, tiga kartu ringkasan, lalu dua
 * kolom berisi jadwal hari ini dan pengumuman.
 *
 * Dirender sebagai satu widget penuh — bukan beberapa widget terpisah —
 * supaya jarak antar bagian dikendalikan langsung di sini dan tidak
 * bergantung pada grid widget Filament.
 *
 * Yang berbeda hanyalah isinya: siswa melihat datanya sendiri, admin
 * melihat data seluruh madrasah.
 */
class DasborMadrasah extends Widget
{
    protected string $view = 'filament.widgets.dasbor-madrasah';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $now = now();

        $jadwal = Schedule::query()
            ->forToday()
            ->with(['subject', 'teacher', 'classroom'])
            ->orderBy('starts_at')
            ->get();

        $siswaAktif = Student::query()->where('is_active', true)->count();
        $siswaNonaktif = Student::query()->where('is_active', false)->count();

        return [
            'nama' => Filament::auth()->user()?->name ?? 'Admin',
            'salam' => $this->salam((int) $now->format('H')),
            'tanggal' => $now->translatedFormat('l, j F Y'),
            'jadwal' => $jadwal,
            'sesiLive' => $jadwal->filter(fn (Schedule $s): bool => $s->isLiveNow())->count(),
            'pengumuman' => Announcement::query()
                ->published()
                ->latest('published_at')
                ->limit(3)
                ->get(),
            // `peran` memetakan tiap kartu ke satu peran palet — primary,
            // secondary, accent — lewat kelas .mk-stat--{peran} di theme.css.
            'statistik' => [
                [
                    'label' => 'Siswa Aktif',
                    'nilai' => number_format($siswaAktif, 0, ',', '.'),
                    'catatan' => $siswaNonaktif > 0
                        ? $siswaNonaktif.' akun nonaktif'
                        : 'Semua akun aktif',
                    'peran' => 'primary',
                    'ikon' => 'heroicon-o-user-group',
                ],
                [
                    'label' => 'Guru & Tendik',
                    'nilai' => number_format(Teacher::query()->count(), 0, ',', '.'),
                    'catatan' => 'Terdaftar di data master',
                    'peran' => 'secondary',
                    'ikon' => 'heroicon-o-academic-cap',
                ],
                [
                    'label' => 'Rombongan Belajar',
                    'nilai' => number_format(Classroom::query()->count(), 0, ',', '.'),
                    'catatan' => Subject::query()->count().' mata pelajaran',
                    'peran' => 'accent',
                    'ikon' => 'heroicon-o-building-library',
                ],
            ],
        ];
    }

    private function salam(int $jam): string
    {
        return match (true) {
            $jam < 11 => 'Selamat pagi',
            $jam < 15 => 'Selamat siang',
            $jam < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }
}
