{{-- Pengumuman terbaru. Variabel: $pengumuman --}}
<section class="mk-panel" aria-labelledby="mk-pengumuman-title">
    <header class="mk-panel__head">
        <h3 id="mk-pengumuman-title" class="mk-display mk-panel__title">Pengumuman</h3>
    </header>

    @if ($pengumuman->isEmpty())
        <p class="mk-empty">Belum ada pengumuman terbit.</p>
    @else
        <ul class="mk-news">
            @foreach ($pengumuman as $p)
                <li class="mk-news__item">
                    <time class="mk-label mk-news__date" datetime="{{ $p->published_at?->toDateString() }}">
                        {{ $p->published_at?->translatedFormat('j F Y') }}
                    </time>
                    <h4 class="mk-display mk-news__title">{{ $p->title }}</h4>
                    <p class="mk-news__body">{{ \Illuminate\Support\Str::limit($p->body, 90) }}</p>
                </li>
            @endforeach
        </ul>
    @endif
</section>
