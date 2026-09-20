<?php

namespace App\Providers;

use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureTables();
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
