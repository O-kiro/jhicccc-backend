{{-- Jadwal hari ini, seluruh kelas. Variabel: $jadwal, $sesiLive --}}
@php
    $labelSesi = $jadwal->count().' sesi'.($sesiLive > 0 ? ' · '.$sesiLive.' live' : '');
@endphp

<section class="mk-panel" aria-labelledby="mk-jadwal-title">
    <header class="mk-panel__head">
        <h3 id="mk-jadwal-title" class="mk-display mk-panel__title">Jadwal Hari Ini</h3>
        <span class="mk-pill">{{ $labelSesi }}</span>
    </header>

    @if ($jadwal->isEmpty())
        <p class="mk-empty">Tidak ada jadwal pelajaran hari ini.</p>
    @else
        <ol class="mk-list">
            @foreach ($jadwal->take(6) as $sesi)
                @php $live = $sesi->isLiveNow(); @endphp

                <li @class(['mk-slot', 'mk-slot--live' => $live])>
                    <span class="mk-display mk-slot__time">
                        {{ substr($sesi->starts_at, 0, 5) }}
                        <span class="mk-slot__until">s/d {{ substr($sesi->ends_at, 0, 5) }}</span>
                    </span>

                    <span class="mk-slot__body">
                        <span class="mk-slot__head">
                            <span class="mk-slot__subject">{{ $sesi->subject->name }}</span>

                            @if ($live)
                                <span class="mk-pill mk-pill--primary">LIVE NOW</span>
                            @endif

                            <span class="mk-pill">{{ $sesi->classroom->name }}</span>
                        </span>

                        <span class="mk-slot__teacher">{{ $sesi->teacher->name }}</span>
                    </span>
                </li>
            @endforeach
        </ol>
    @endif
</section>
