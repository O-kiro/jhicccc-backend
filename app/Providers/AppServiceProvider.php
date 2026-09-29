<?php

namespace App\Providers;

use App\Services\RevalidasiSitus;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Satu instans per proses, supaya beberapa perubahan dalam satu
        // permintaan hanya memicu satu panggilan ke situs.
        $this->app->singleton(RevalidasiSitus::class);
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Satu callback untuk seluruh umur proses. Lihat RevalidasiSitus.
        $this->app->terminating(fn () => $this->app->make(RevalidasiSitus::class)->kirimBilaDiminta());

        $this->configureTables();
        $this->configureRateLimits();
        $this->configureDiBelakangProxy();
    }

    /**
     * Membuat URL dari APP_URL, bukan dari host yang terlihat.
     *
     * Di server, reverse proxy meneruskan permintaan ke 127.0.0.1:8000. Kalau
     * penerusannya memakai `RewriteRule [P]` di .htaccess — satu-satunya cara
     * di panel yang menimpa ulang VirtualHost-nya — `ProxyPreserveHost` tidak
     * bisa disetel, sehingga Laravel menerima `Host: 127.0.0.1:8000`.
     *
     * Akibatnya nyata dan membingungkan: sesudah login, panel admin
     * mengalihkan pengunjung ke http://127.0.0.1:8000 — alamat yang hanya ada
     * di dalam server, jadi halamannya tidak bisa dibuka sama sekali. Aset
     * Filament juga ditautkan dengan http://, lalu diblokir browser sebagai
     * mixed content dan panel tampil tanpa gaya.
     *
     * Hanya aktif kalau APP_URL benar-benar diisi alamat http(s) — di lokal
     * nilainya localhost dan tidak ada yang perlu dipaksa.
     */
    private function configureDiBelakangProxy(): void
    {
        $appUrl = (string) config('app.url');

        if (! str_starts_with($appUrl, 'http://') && ! str_starts_with($appUrl, 'https://')) {
            return;
        }

        URL::forceRootUrl($appUrl);

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Batas laju untuk endpoint yang dipakai lebih dari satu jenis akun.
     *
     * `throttle:5,1` biasa mengunci hitungan pada ID pengguna saja — siswa #1
     * dan guru #1 akan berbagi jatah yang sama. Kunci di sini ikut memuat
     * jenis akunnya.
     */
    private function configureRateLimits(): void
    {
        $kunci = fn (Request $request): string => $request->user()
            ? class_basename($request->user()).':'.$request->user()->getAuthIdentifier()
            : (string) $request->ip();

        // Memeriksa sandi lama; tanpa batas ketat bisa dipakai menebaknya
        // dari sesi yang tertinggal terbuka.
        RateLimiter::for('portal-sandi', fn (Request $request) => Limit::perMinute(5)->by($kunci($request)));

        RateLimiter::for('portal-tulis', fn (Request $request) => Limit::perMinute(30)->by($kunci($request)));
    }

    /**
     * Setelan bersama untuk seluruh tabel Filament.
     *
     * Filter ditaruh berjajar di atas tabel — bukan di dalam dropdown seperti
     * bawaan Filament — mengikuti tata letak MAKOBADIG, agar penyaring yang
     * sering dipakai langsung terlihat tanpa perlu dibuka dulu.
     */
    private function configureTables(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->filtersLayout(FiltersLayout::AboveContent)
                ->paginationPageOptions([10, 25, 50, 100])
                ->defaultPaginationPageOption(10);
        });
    }
}
