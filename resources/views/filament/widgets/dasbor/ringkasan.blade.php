{{--
    Tiga kartu ringkasan. Variabel: $statistik
    Tiap entri: label, nilai, catatan, peran (primary|secondary|accent), ikon.
--}}
<section class="mk-stats" aria-label="Ringkasan madrasah">
    @foreach ($statistik as $kartu)
        <article class="mk-stat mk-stat--{{ $kartu['peran'] }}">
            <div>
                <p class="mk-label mk-stat__label">{{ $kartu['label'] }}</p>
                <p class="mk-display mk-stat__value">{{ $kartu['nilai'] }}</p>
                <p class="mk-stat__note">{{ $kartu['catatan'] }}</p>
            </div>

            <span class="mk-stat__icon" aria-hidden="true">
                {{ svg($kartu['ikon']) }}
            </span>
        </article>
    @endforeach
</section>
