{{-- Panel sapaan. Variabel: $salam, $nama, $tanggal --}}
<section class="mk-hero" aria-labelledby="mk-hero-title">
    <span class="mk-hero__orb mk-hero__orb--a" aria-hidden="true"></span>
    <span class="mk-hero__orb mk-hero__orb--b" aria-hidden="true"></span>

    <div class="mk-hero__body">
        <p class="mk-label mk-hero__eyebrow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                <rect x="6" y="6" width="12" height="12" rx="1" />
                <rect x="6" y="6" width="12" height="12" rx="1" transform="rotate(45 12 12)" />
            </svg>
            Portal Administrasi
        </p>

        <h2 id="mk-hero-title" class="mk-display mk-hero__title">
            {{ $salam }}, {{ $nama }}
        </h2>

        <p class="mk-hero__quote">
            &ldquo;Melayani dengan tertib adalah bagian dari ibadah — data yang rapi hari ini
            memudahkan keputusan esok hari.&rdquo;
        </p>

        <div class="mk-hero__actions">
            <span class="mk-chip">{{ $tanggal }}</span>

            {{-- Hanya tampil bagi peran yang boleh membukanya; selebihnya tombol
                 ini cuma berujung halaman 403. --}}
            @if (\App\Filament\Resources\Students\StudentResource::canAccess())
                <a href="{{ \App\Filament\Resources\Students\StudentResource::getUrl() }}"
                   class="mk-btn mk-btn--light">
                    Kelola Siswa
                </a>
            @endif

            @if (\App\Filament\Resources\Schedules\ScheduleResource::canAccess())
                <a href="{{ \App\Filament\Resources\Schedules\ScheduleResource::getUrl() }}"
                   class="mk-btn mk-btn--ghost">
                    Atur Jadwal
                </a>
            @endif
        </div>
    </div>
</section>
