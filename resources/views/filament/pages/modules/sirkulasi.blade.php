<x-filament-panels::page>
    @php
        $peminjam = $this->getPeminjam();
        $aktif = $peminjam ? $this->getPinjamanAktif() : collect();
        $guru = $peminjam instanceof \App\Models\Teacher;
    @endphp

    {{-- 1. Kartu peminjam --}}
    <form wire:submit="cariSiswa" class="mk-desk">
        <label for="nisn" class="mk-label">1. Tap kartu, atau ketik NISN siswa / NIP guru</label>
        <div class="mk-desk__row">
            <input id="nisn" type="text" wire:model="nisn" class="mk-input mk-input--wide"
                   placeholder="NISN atau NIP" autocomplete="off" autofocus />
            <x-filament::button type="submit">Cari</x-filament::button>
            @if ($peminjam)
                <x-filament::button color="gray" wire:click="selesai" type="button">Peminjam berikutnya</x-filament::button>
            @endif
        </div>
        @error('nisn') <p class="mk-error">{{ $message }}</p> @enderror
    </form>

    @if ($peminjam)
        <div class="mk-stat-row">
            <div class="mk-stat">
                <div class="mk-stat__label">{{ $guru ? 'Guru' : 'Siswa' }}</div>
                <div class="mk-desk__name">{{ $peminjam->name }}</div>
                <div class="mk-hint">
                    @if ($guru)
                        NIP {{ $peminjam->nip ?? '—' }}
                    @else
                        NISN {{ $peminjam->nisn }} &middot; Kelas {{ $peminjam->classroom?->name ?? '—' }}
                    @endif
                </div>
            </div>
            <div class="mk-stat">
                <div class="mk-stat__label">Sedang dipinjam</div>
                <div class="mk-stat__value">{{ $aktif->count() }} / {{ $this->getKuota() }}</div>
            </div>
        </div>

        {{-- 2. Pinjam --}}
        <form wire:submit="pinjam" class="mk-desk">
            <label for="buku" class="mk-label">2. Pindai kode buku, atau pilih dari daftar</label>
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
                <x-filament::button type="submit" :disabled="$aktif->count() >= $this->getKuota()">
                    Proses Peminjaman
                </x-filament::button>
            </div>
            @error('buku') <p class="mk-error">{{ $message }}</p> @enderror
            @error('durasi') <p class="mk-error">{{ $message }}</p> @enderror
        </form>

        {{-- 3. Kembalikan --}}
        <h2 class="mk-section-title">Sedang Dipinjam</h2>
        @if ($aktif->isEmpty())
            <p class="mk-empty">Tidak ada buku yang sedang dipinjam {{ $guru ? 'guru' : 'siswa' }} ini.</p>
        @else
            <div class="mk-scroll">
                <table class="mk-matrix">
                    <thead>
                        <tr><th>Buku</th><th>Jatuh Tempo</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($aktif as $p)
                            @php $sisa = $p->daysUntilDue(); @endphp
                            <tr>
                                <td class="mk-sticky">{{ $p->book->title }}</td>
                                <td>{{ $p->due_on->translatedFormat('j M Y') }}</td>
                                <td>{{ $sisa < 0 ? 'Terlambat '.abs($sisa).' hari' : 'Sisa '.$sisa.' hari' }}</td>
                                <td>
                                    <x-filament::button size="xs" color="success"
                                        wire:click="kembalikan({{ $p->id }})"
                                        wire:confirm="Kembalikan {{ $p->book->title }}?">
                                        Kembalikan
                                    </x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @error('pinjaman') <p class="mk-error">{{ $message }}</p> @enderror
    @endif
</x-filament-panels::page>
