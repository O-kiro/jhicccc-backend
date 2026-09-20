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
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
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
            // brandName tetap diisi: dipakai untuk <title> halaman dan teks
            // alternatif, sementara tampilan brand-nya dari brandLogo.
            ->brandName('MAN Kota Batu')
            ->brandLogo(fn (): View => view('filament.brand'))
            ->brandLogoHeight('2.25rem')
            // 6xl = 72rem, sama dengan `max-w-6xl` yang membungkus halaman
            // portal siswa, supaya lebar kontennya identik.
            ->maxContentWidth(Width::SixExtraLarge)
            // Harus sama dengan --mk-primary di theme.css: tombol, tautan, dan
            // cincin fokus bawaan Filament membaca palet ini, bukan token --mk-*.
            ->colors([
                'primary' => Color::hex('#0f6e56'),
                'gray' => Color::hex('#636361'),
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
            // Dua bagian tata letak MAKOBADIG yang tidak disediakan Filament:
            // judul halaman di topbar, dan kartu pengguna di dasar sidebar.
            //
            // Judul memakai TOPBAR_LOGO_AFTER, bukan TOPBAR_START: yang kedua
            // dirender SEBELUM brand, sehingga judul berdempetan di atas kolom
            // sidebar. TOPBAR_LOGO_AFTER menaruhnya tepat setelah brand, dan
            // theme.css membuat brand selebar sidebar agar judul jatuh di
            // kolom konten.
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn (): View => view('filament.hooks.topbar-title'),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): View => view('filament.hooks.sidebar-user'),
            )
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
