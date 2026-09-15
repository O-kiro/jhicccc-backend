{{--
    Tata letak dan nilai gaya di sini menyalin halaman Overview portal siswa
    (app/siswa/(portal)/page.tsx). Ditulis dengan gaya inline + token --mk-*
    karena Filament v5 mengompilasi CSS-nya sendiri, sehingga kelas utilitas
    Tailwind sembarangan belum tentu ikut terbangun.
--}}
@php
    $warna = [
        'teal' => ['bg' => 'var(--mk-teal-soft)', 'fg' => 'var(--mk-teal)'],
        'blue' => ['bg' => 'var(--mk-blue-soft)', 'fg' => 'var(--mk-blue)'],
        'gold' => ['bg' => 'var(--mk-gold-soft)', 'fg' => 'var(--mk-gold-strong)'],
    ];
@endphp

<div style="display:grid;gap:1.5rem;">

    {{-- Sapaan — setara panel hero di /siswa --}}
    <section
        style="position:relative;overflow:hidden;border-radius:var(--mk-radius-panel);
               padding:2.5rem 1.75rem;color:var(--mk-on-dark);
               background-image:var(--mk-teal-gradient);"
    >
        <div aria-hidden="true"
             style="position:absolute;top:-4rem;right:-4rem;width:14rem;height:14rem;
                    border-radius:9999px;background:rgba(255,255,255,0.05);"></div>
        <div aria-hidden="true"
             style="position:absolute;bottom:-6rem;right:6rem;width:10rem;height:10rem;
                    border-radius:9999px;background:rgba(255,255,255,0.05);"></div>

        <div style="position:relative;max-width:42rem;">
            <span style="display:inline-flex;align-items:center;gap:0.5rem;font-size:0.8125rem;
                         font-weight:600;letter-spacing:0.04em;text-transform:uppercase;
                         color:var(--mk-gold);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                     style="width:0.875rem;height:0.875rem;" aria-hidden="true">
                    <rect x="6" y="6" width="12" height="12" rx="1" />
                    <rect x="6" y="6" width="12" height="12" rx="1" transform="rotate(45 12 12)" />
                </svg>
                Portal Administrasi
            </span>

            <h2 style="margin:0.75rem 0 0;font-size:clamp(1.75rem,3.6vw,2.75rem);font-weight:800;
                       line-height:1.15;letter-spacing:-0.03em;
                       font-family:'Bricolage Grotesque','Inter',ui-sans-serif,system-ui,sans-serif;">
                {{ $salam }}, {{ $nama }}
            </h2>

            <p style="margin:1rem 0 0;font-family:Lora,ui-serif,Georgia,serif;font-style:italic;
                      font-size:1.0625rem;line-height:1.7;color:rgba(243,241,233,0.8);">
                &ldquo;Melayani dengan tertib adalah bagian dari ibadah — data yang rapi hari ini
                memudahkan keputusan esok hari.&rdquo;
            </p>

            <div style="margin-top:1.75rem;display:flex;flex-wrap:wrap;align-items:center;gap:0.75rem;">
                <span style="display:inline-flex;align-items:center;gap:0.5rem;border-radius:9999px;
                             background:rgba(255,255,255,0.12);padding:0.5rem 1rem;
                             font-size:0.875rem;font-weight:600;">
                    {{ $tanggal }}
                </span>

                <a href="{{ \App\Filament\Resources\Students\StudentResource::getUrl() }}"
                   class="mk-press"
                   style="display:inline-flex;align-items:center;gap:0.5rem;border-radius:9999px;
                          background:var(--mk-surface);padding:0.625rem 1.25rem;font-size:0.875rem;
                          font-weight:600;color:var(--mk-teal);text-decoration:none;">
                    Kelola Siswa
                </a>

                <a href="{{ \App\Filament\Resources\Schedules\ScheduleResource::getUrl() }}"
                   class="mk-press"
                   style="display:inline-flex;align-items:center;gap:0.5rem;border-radius:9999px;
                          border:1px solid rgba(255,255,255,0.3);padding:0.625rem 1.25rem;
                          font-size:0.875rem;font-weight:600;color:inherit;text-decoration:none;">
                    Atur Jadwal
                </a>
            </div>
        </div>
    </section>

    {{-- Ringkasan — setara tiga StatCard di /siswa --}}
    <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(14rem,1fr));">
        @foreach ($statistik as $s)
            @php $c = $warna[$s['warna']]; @endphp
            <div class="mk-card mk-card-hover">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem;">
                    <div>
                        <p style="margin:0;font-size:0.75rem;font-weight:600;letter-spacing:0.06em;
                                  text-transform:uppercase;color:var(--mk-muted);">
                            {{ $s['label'] }}
                        </p>
                        <p style="margin:0.5rem 0 0;font-size:1.875rem;font-weight:800;
                                  font-variant-numeric:tabular-nums;color:var(--mk-ink);
                                  font-family:'Bricolage Grotesque','Inter',ui-sans-serif,system-ui,sans-serif;">
                            {{ $s['nilai'] }}
                        </p>
                        <p style="margin:0.25rem 0 0;font-size:0.75rem;font-weight:600;color:{{ $c['fg'] }};">
                            {{ $s['catatan'] }}
                        </p>
                    </div>
                    <span style="display:inline-grid;place-items:center;width:2.5rem;height:2.5rem;
                                 flex-shrink:0;border-radius:0.75rem;background:{{ $c['bg'] }};
                                 color:{{ $c['fg'] }};font-weight:800;">
                        {{ mb_substr($s['label'], 0, 1) }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Dua kolom — setara "Jadwal Hari Ini" + "Pengumuman" di /siswa --}}
    <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(20rem,1fr));">

        <section class="mk-card" style="grid-column:span 1;">
            <div style="display:flex;align-items:center;justify-content:space-between;
                        gap:1rem;margin-bottom:1.25rem;">
                <h3 style="margin:0;font-size:1.125rem;font-weight:800;color:var(--mk-ink);
                           font-family:'Bricolage Grotesque','Inter',ui-sans-serif,system-ui,sans-serif;">
                    Jadwal Hari Ini
                </h3>
                @php
                    // Disusun di PHP, bukan dengan @if inline: direktif Blade yang
                    // menempel langsung setelah huruf (mis. "sesi@if") tidak dikenali.
                    $labelSesi = $jadwal->count().' sesi'.($sesiLive > 0 ? ' · '.$sesiLive.' live' : '');
                @endphp
                <span style="border-radius:9999px;background:var(--mk-surface-2);padding:0.25rem 0.625rem;
                             font-size:0.6875rem;font-weight:600;color:var(--mk-muted);">
                    {{ $labelSesi }}
                </span>
            </div>

            @if ($jadwal->isEmpty())
                <p style="margin:0;border-radius:0.75rem;border:1px solid var(--mk-line);
                          background:var(--mk-surface-2);padding:1.25rem;font-size:0.875rem;
                          color:var(--mk-muted);">
                    Tidak ada jadwal pelajaran hari ini.
                </p>
            @else
                <ol style="margin:0;padding:0;list-style:none;display:grid;gap:0.625rem;">
                    @foreach ($jadwal->take(6) as $sesi)
                        @php $live = $sesi->isLiveNow(); @endphp
                        <li style="display:flex;flex-wrap:wrap;align-items:center;gap:1rem;
                                   border-radius:0.75rem;padding:0.875rem;
                                   border:1px solid {{ $live ? 'color-mix(in oklab, var(--mk-teal) 35%, transparent)' : 'var(--mk-line)' }};
                                   background:{{ $live ? 'color-mix(in oklab, var(--mk-teal-soft) 40%, transparent)' : 'var(--mk-surface-2)' }};">
                            <span style="width:6.5rem;flex-shrink:0;font-size:0.875rem;font-weight:800;
                                         font-variant-numeric:tabular-nums;color:var(--mk-ink);
                                         font-family:'Bricolage Grotesque','Inter',ui-sans-serif,system-ui,sans-serif;">
                                {{ substr($sesi->starts_at, 0, 5) }}
                                <span style="display:block;font-size:0.6875rem;font-weight:600;color:var(--mk-muted);">
                                    s/d {{ substr($sesi->ends_at, 0, 5) }}
                                </span>
                            </span>
                            <span style="min-width:0;flex:1;">
                                <span style="display:flex;flex-wrap:wrap;align-items:center;gap:0.5rem;">
                                    <span style="font-weight:600;color:var(--mk-ink);">{{ $sesi->subject->name }}</span>
                                    @if ($live)
                                        <span style="display:inline-flex;align-items:center;gap:0.375rem;
                                                     border-radius:9999px;background:var(--mk-teal-soft);
                                                     color:var(--mk-teal);padding:0.125rem 0.5rem;
                                                     font-size:0.6875rem;font-weight:700;">
                                            LIVE NOW
                                        </span>
                                    @endif
                                    <span style="border-radius:9999px;background:var(--mk-surface-2);
                                                 padding:0.125rem 0.5rem;font-size:0.6875rem;
                                                 font-weight:600;color:var(--mk-muted);">
                                        {{ $sesi->classroom->name }}
                                    </span>
                                </span>
                                <span style="display:block;margin-top:0.125rem;font-size:0.75rem;
                                             color:var(--mk-muted);">
                                    {{ $sesi->teacher->name }}
                                </span>
                            </span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        <section class="mk-card">
            <h3 style="margin:0 0 1.25rem;font-size:1.125rem;font-weight:800;color:var(--mk-ink);
                       font-family:'Bricolage Grotesque','Inter',ui-sans-serif,system-ui,sans-serif;">
                Pengumuman
            </h3>

            @if ($pengumuman->isEmpty())
                <p style="margin:0;font-size:0.875rem;color:var(--mk-muted);">
                    Belum ada pengumuman terbit.
                </p>
            @else
                <ul style="margin:0;padding:0;list-style:none;">
                    @foreach ($pengumuman as $p)
                        <li style="padding:0.875rem 0;
                                   {{ ! $loop->last ? 'border-bottom:1px solid var(--mk-line);' : '' }}">
                            <time datetime="{{ $p->published_at?->toDateString() }}"
                                  style="font-size:0.6875rem;font-weight:600;letter-spacing:0.06em;
                                         text-transform:uppercase;color:var(--mk-gold-strong);">
                                {{ $p->published_at?->translatedFormat('j F Y') }}
                            </time>
                            <p style="margin:0.375rem 0 0;font-size:0.875rem;font-weight:800;
                                      color:var(--mk-ink);
                                      font-family:'Bricolage Grotesque','Inter',ui-sans-serif,system-ui,sans-serif;">
                                {{ $p->title }}
                            </p>
                            <p style="margin:0.25rem 0 0;font-size:0.75rem;line-height:1.6;color:var(--mk-muted);">
                                {{ \Illuminate\Support\Str::limit($p->body, 90) }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
