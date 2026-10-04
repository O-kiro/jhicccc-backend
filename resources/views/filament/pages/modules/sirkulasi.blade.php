<x-filament-panels::page>
    @php
        $peminjam = $this->getPeminjam();
        $aktif = $peminjam ? $this->getPinjamanAktif() : collect();
        $guru = $peminjam instanceof \App\Models\Teacher;
        $kuota = $this->getKuota();
        $penuh = $aktif->count() >= $kuota;
        $ringkas = $this->getRingkasan();
        $terlambat = $this->getTerlambat();
        $aktivitas = $this->getAktivitas();
        $namaPeminjam = fn ($p) => $p->teacher?->name ?? $p->student?->name ?? '—';
    @endphp

    {{-- Ringkasan hari ini --}}
    <div class="mk-sirk-stats">
        @foreach ([
            ['Sedang dipinjam', $ringkas['aktif'], 'heroicon-o-book-open', 'primary'],
            ['Dipinjam hari ini', $ringkas['pinjam_hari_ini'], 'heroicon-o-arrow-up-tray', 'secondary'],
            ['Dikembalikan hari ini', $ringkas['kembali_hari_ini'], 'heroicon-o-arrow-down-tray', 'primary'],
            ['Lewat tempo', $ringkas['terlambat'], 'heroicon-o-exclamation-triangle', $ringkas['terlambat'] ? 'danger' : 'muted'],
        ] as [$label, $nilai, $ikon, $warna])
            <div class="mk-sirk-stat mk-sirk-stat--{{ $warna }}">
                <span class="mk-sirk-stat__icon"><x-filament::icon :icon="$ikon" /></span>
                <div>
                    <div class="mk-sirk-stat__value">{{ $nilai }}</div>
                    <div class="mk-sirk-stat__label">{{ $label }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mk-sirk-grid">
        {{-- Kolom kiri: melayani peminjam --}}
        <section class="mk-sirk-card">
            <header class="mk-sirk-card__head">
                <h2 class="mk-sirk-card__title">Layani Peminjam</h2>
                @if ($peminjam)
                    <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-path" wire:click="selesai" type="button">
                        Peminjam berikutnya
                    </x-filament::button>
                @endif
            </header>

            {{-- 1. Kartu peminjam --}}
            <form wire:submit="cariSiswa" class="mk-sirk-step">
                <span class="mk-sirk-step__num {{ $peminjam ? 'is-done' : '' }}">1</span>
                <div class="mk-sirk-step__body">
                    <label for="nisn" class="mk-sirk-step__label">Tap kartu, atau ketik NISN siswa / NIP guru</label>
                    <div class="mk-desk__row">
                        <input id="nisn" type="text" wire:model="nisn" class="mk-input mk-input--wide"
                               placeholder="NISN atau NIP" autocomplete="off" autofocus />
                        <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">Cari</x-filament::button>
                    </div>
                    @error('nisn') <p class="mk-error">{{ $message }}</p> @enderror

                    @if ($peminjam)
                        <div class="mk-sirk-person">
                            <span class="mk-sirk-person__avatar">{{ mb_strtoupper(mb_substr($peminjam->name, 0, 1)) }}</span>
                            <div class="mk-sirk-person__text">
                                <div class="mk-sirk-person__name">{{ $peminjam->name }}</div>
                                <div class="mk-sirk-person__meta">
                                    <span class="mk-sirk-badge">{{ $guru ? 'Guru' : 'Siswa' }}</span>
                                    @if ($guru)
                                        NIP {{ $peminjam->nip ?? '—' }}
                                    @else
                                        NISN {{ $peminjam->nisn }} · Kelas {{ $peminjam->classroom?->name ?? '—' }}
                                    @endif
                                </div>
                            </div>
                            <div class="mk-sirk-quota {{ $penuh ? 'is-full' : '' }}">
                                <span>{{ $aktif->count() }}/{{ $kuota }}</span>
                                <small>pinjaman</small>
                            </div>
                        </div>
                    @endif
                </div>
            </form>

            {{-- 2. Pinjam --}}
            <form wire:submit="pinjam" class="mk-sirk-step {{ $peminjam ? '' : 'is-locked' }}">
                <span class="mk-sirk-step__num">2</span>
                <div class="mk-sirk-step__body">
                    <label for="buku" class="mk-sirk-step__label">Pindai kode buku, atau pilih dari daftar</label>
                    @if ($peminjam)
                        <div class="mk-desk__row">
                            <input id="buku" type="text" wire:model="buku" class="mk-input mk-input--wide"
                                   placeholder="Kode buku" autocomplete="off" list="daftar-buku" />
                            <datalist id="daftar-buku">
                                @foreach ($this->getBukuOptions() as $id => $judul)
                                    <option value="{{ $id }}">{{ $judul }}</option>
                                @endforeach
                            </datalist>
                            <select wire:model="durasi" class="mk-input" aria-label="Lama pinjam">
                                @foreach ($this->getDurasiOptions() as $hari => $label)
                                    <option value="{{ $hari }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-filament::button type="submit" icon="heroicon-o-check" :disabled="$penuh">
                                Proses Peminjaman
                            </x-filament::button>
                        </div>
                        @if ($penuh)
                            <p class="mk-sirk-note">Kuota pinjaman penuh — kembalikan buku dulu.</p>
                        @endif
                        @error('buku') <p class="mk-error">{{ $message }}</p> @enderror
                        @error('durasi') <p class="mk-error">{{ $message }}</p> @enderror
                    @else
                        <p class="mk-sirk-note">Terbuka setelah peminjam ditemukan.</p>
                    @endif
                </div>
            </form>

            {{-- 3. Kembalikan --}}
            <div class="mk-sirk-step {{ $peminjam ? '' : 'is-locked' }}">
                <span class="mk-sirk-step__num">3</span>
                <div class="mk-sirk-step__body">
                    <span class="mk-sirk-step__label">Buku yang sedang dipinjam</span>
                    @if (! $peminjam)
                        <p class="mk-sirk-note">Daftar pinjaman peminjam tampil di sini.</p>
                    @elseif ($aktif->isEmpty())
                        <p class="mk-sirk-note">Tidak ada buku yang sedang dipinjam {{ $guru ? 'guru' : 'siswa' }} ini.</p>
                    @else
                        <ul class="mk-sirk-list">
                            @foreach ($aktif as $p)
                                @php $sisa = $p->daysUntilDue(); @endphp
                                <li class="mk-sirk-loan">
                                    <span class="mk-sirk-loan__icon"><x-filament::icon icon="heroicon-o-book-open" /></span>
                                    <div class="mk-sirk-loan__text">
                                        <div class="mk-sirk-loan__title">{{ $p->book->title }}</div>
                                        <div class="mk-sirk-loan__meta">
                                            Tempo {{ $p->due_on->translatedFormat('j M Y') }}
                                            <span class="mk-sirk-pill {{ $sisa < 0 ? 'is-late' : ($sisa <= 2 ? 'is-soon' : '') }}">
                                                {{ $sisa < 0 ? 'Terlambat '.abs($sisa).' hari' : ($sisa === 0 ? 'Hari ini' : 'Sisa '.$sisa.' hari') }}
                                            </span>
                                        </div>
                                    </div>
                                    <x-filament::button size="sm" color="success" icon="heroicon-o-arrow-uturn-left"
                                        wire:click="kembalikan({{ $p->id }})"
                                        wire:confirm="Kembalikan {{ $p->book->title }}?">
                                        Kembalikan
                                    </x-filament::button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @error('pinjaman') <p class="mk-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Kolom kanan: perlu perhatian & aktivitas --}}
        <div class="mk-sirk-side">
            <section class="mk-sirk-card">
                <header class="mk-sirk-card__head">
                    <h2 class="mk-sirk-card__title">Lewat Tempo</h2>
                    @if ($ringkas['terlambat'] > $terlambat->count())
                        <span class="mk-sirk-pill is-late">+{{ $ringkas['terlambat'] - $terlambat->count() }} lainnya</span>
                    @endif
                </header>
                @if ($terlambat->isEmpty())
                    <p class="mk-sirk-note">Tidak ada pinjaman yang lewat tempo.</p>
                @else
                    <ul class="mk-sirk-list">
                        @foreach ($terlambat as $p)
                            <li>
                                <button type="button" class="mk-sirk-row" wire:click="layani({{ $p->id }})" title="Layani peminjam ini">
                                    <div class="mk-sirk-row__text">
                                        <div class="mk-sirk-row__title">{{ $namaPeminjam($p) }}</div>
                                        <div class="mk-sirk-row__meta">{{ $p->book->title }}</div>
                                    </div>
                                    <span class="mk-sirk-pill is-late">{{ abs($p->daysUntilDue()) }} hari</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="mk-sirk-card">
                <header class="mk-sirk-card__head">
                    <h2 class="mk-sirk-card__title">Aktivitas Terakhir</h2>
                </header>
                @if ($aktivitas->isEmpty())
                    <p class="mk-sirk-note">Belum ada transaksi.</p>
                @else
                    <ul class="mk-sirk-list">
                        @foreach ($aktivitas as $p)
                            @php $kembali = $p->returned_at !== null; @endphp
                            <li class="mk-sirk-activity">
                                <span class="mk-sirk-activity__dot {{ $kembali ? 'is-in' : 'is-out' }}">
                                    <x-filament::icon :icon="$kembali ? 'heroicon-m-arrow-down' : 'heroicon-m-arrow-up'" />
                                </span>
                                <div class="mk-sirk-row__text">
                                    <div class="mk-sirk-row__title">{{ $p->book->title }}</div>
                                    <div class="mk-sirk-row__meta">
                                        {{ $kembali ? 'Dikembalikan' : 'Dipinjam' }} {{ $namaPeminjam($p) }}
                                        · {{ $p->updated_at?->diffForHumans() }}
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-filament-panels::page>
