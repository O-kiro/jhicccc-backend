<x-filament-panels::page>
    @php
        $tanggal = $this->getTanggalList();
        $hari = $this->getHariList();
        $libur = $this->getAkhirPekan();
        $baris = $this->getBaris();
        $kodeList = \App\Models\Attendance::KODE;
        $ringkas = $this->getRingkasan($baris);
        $periode = $this->getPeriode();
        $hariIni = now()->isSameMonth($periode) ? now()->day : null;
        $persen = fn (?float $p) => $p === null ? '—' : rtrim(rtrim(number_format($p, 1, ',', ''), '0'), ',').'%';
    @endphp

    {{-- Penyaring --}}
    <div class="mk-sirk-card mk-rekap-filter">
        <div class="mk-rekap-month">
            <button type="button" wire:click="bulanSebelumnya" class="mk-rekap-nav" aria-label="Bulan sebelumnya">
                <x-filament::icon icon="heroicon-m-chevron-left" />
            </button>
            <div>
                <div class="mk-rekap-month__title">{{ $periode->translatedFormat('F Y') }}</div>
                <div class="mk-sirk-note">{{ count($tanggal) }} hari · {{ $ringkas['siswa'] }} siswa aktif</div>
            </div>
            <button type="button" wire:click="bulanBerikutnya" class="mk-rekap-nav" aria-label="Bulan berikutnya">
                <x-filament::icon icon="heroicon-m-chevron-right" />
            </button>
        </div>
        <div class="mk-rekap-fields">
            <div>
                <label for="bulan" class="mk-label">Bulan</label>
                <input id="bulan" type="month" wire:model.live="bulan" class="mk-input" />
            </div>
            <div>
                <label for="kelas" class="mk-label">Kelas</label>
                <select id="kelas" wire:model.live="kelas" class="mk-input">
                    <option value="">Semua kelas</option>
                    @foreach ($this->getKelasOptions() as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Ringkasan bulan --}}
    <div class="mk-sirk-stats">
        <div class="mk-sirk-stat mk-sirk-stat--primary">
            <span class="mk-sirk-stat__icon"><x-filament::icon icon="heroicon-o-chart-pie" /></span>
            <div>
                <div class="mk-sirk-stat__value">{{ $persen($ringkas['persen']) }}</div>
                <div class="mk-sirk-stat__label">Kehadiran bulan ini</div>
            </div>
        </div>
        @foreach ([
            ['T', 'heroicon-o-clock', 'accent'],
            ['S', 'heroicon-o-heart', 'secondary'],
            ['I', 'heroicon-o-envelope', 'secondary'],
            ['A', 'heroicon-o-x-circle', $ringkas['rekap']['A'] ? 'danger' : 'muted'],
        ] as [$k, $ikon, $warna])
            <div class="mk-sirk-stat mk-sirk-stat--{{ $warna }}">
                <span class="mk-sirk-stat__icon"><x-filament::icon :icon="$ikon" /></span>
                <div>
                    <div class="mk-sirk-stat__value">{{ $ringkas['rekap'][$k] }}</div>
                    <div class="mk-sirk-stat__label">{{ $kodeList[$k] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Matriks --}}
    <section class="mk-sirk-card">
        <header class="mk-sirk-card__head">
            <h2 class="mk-sirk-card__title">Buku Induk Kehadiran</h2>
            <div class="mk-rekap-legend">
                @foreach ($kodeList as $k => $arti)
                    <span class="mk-rekap-chip mk-kode-{{ strtolower($k) }}"><b>{{ $k }}</b> {{ $arti }}</span>
                @endforeach
            </div>
        </header>

        @if ($baris->isEmpty())
            <p class="mk-sirk-note">Belum ada siswa aktif pada filter ini.</p>
        @else
            <div class="mk-rekap-scroll">
                <table class="mk-rekap-table">
                    <thead>
                        <tr>
                            <th class="mk-rekap-name" rowspan="2">Siswa</th>
                            @foreach ($tanggal as $t)
                                <th @class(['mk-rekap-day', 'is-weekend' => in_array($t, $libur), 'is-today' => $t === $hariIni])>{{ $hari[$t] }}</th>
                            @endforeach
                            @foreach (array_keys($kodeList) as $k)
                                <th class="mk-rekap-sum" rowspan="2">{{ $k }}</th>
                            @endforeach
                            <th class="mk-rekap-sum mk-rekap-pct" rowspan="2">% Hadir</th>
                        </tr>
                        <tr>
                            @foreach ($tanggal as $t)
                                <th @class(['mk-rekap-day mk-rekap-day--num', 'is-weekend' => in_array($t, $libur), 'is-today' => $t === $hariIni])>{{ $t }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($baris as $row)
                            @php $p = \App\Filament\Pages\Modules\RekapAbsensi::persenHadir($row['rekap']); @endphp
                            <tr>
                                <td class="mk-rekap-name">
                                    <div class="mk-rekap-name__main">{{ $row['siswa']->name }}</div>
                                    <div class="mk-rekap-name__sub">{{ $row['siswa']->classroom?->name ?? '—' }}</div>
                                </td>
                                @foreach ($tanggal as $t)
                                    @php $kode = $row['kode'][$t] ?? null; @endphp
                                    <td @class(['mk-rekap-day', 'is-weekend' => in_array($t, $libur), 'is-today' => $t === $hariIni])>
                                        @if ($kode)
                                            <span class="mk-rekap-code mk-kode-{{ strtolower($kode) }}" title="{{ $kodeList[$kode] ?? $kode }}">{{ $kode }}</span>
                                        @endif
                                    </td>
                                @endforeach
                                @foreach (array_keys($kodeList) as $k)
                                    <td class="mk-rekap-sum">{{ $row['rekap'][$k] ?: '' }}</td>
                                @endforeach
                                <td @class(['mk-rekap-sum mk-rekap-pct', 'is-low' => $p !== null && $p < 85])>{{ $persen($p) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mk-sirk-note mk-rekap-foot">
                % hadir = (Hadir + Terlambat) ÷ hari tercatat selain Libur. Di bawah 85% ditandai merah.
            </p>
        @endif
    </section>
</x-filament-panels::page>
