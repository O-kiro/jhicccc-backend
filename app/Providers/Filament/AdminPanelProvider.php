<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\DasborMadrasah;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->brandName('MAN Kota Batu')
            // Teal #0f6e56 — warna utama yang sama dengan situs publik.
            ->colors([
                'primary' => Color::hex('#0f6e56'),
                'gray' => Color::hex('#6c6a61'),
            ])
            // Urutan mengikuti sidebar di dashboard-admin-tour.md.
            ->navigationGroups([
                'Data Master',
                'Akademik',
                'Kesiswaan',
                'Absensi',
                'Humas',
                'Keuangan',
                'Sarana & Prasarana',
                'E-Library',
                'Konten',
                'Lainnya',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            // Satu widget penuh yang menyalin susunan halaman Overview portal
            // siswa. AccountWidget dan FilamentInfoWidget bawaan tidak dipakai:
            // yang pertama mengulang menu pengguna di topbar, yang kedua
            // promosi framework yang tidak relevan bagi staf madrasah.
            ->widgets([
                DasborMadrasah::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
