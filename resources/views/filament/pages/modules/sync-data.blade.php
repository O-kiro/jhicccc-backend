<x-filament-panels::page>
    <p class="mk-hint">
        Unggah berkas CSV hasil ekspor EMIS atau Excel. Kolom yang dikenali:
        <strong>nisn</strong> dan <strong>nama</strong> (wajib), <strong>kelas</strong> dan
        <strong>aktif</strong> (opsional). Pemisah koma maupun titik koma sama-sama dibaca.
        Siswa dicocokkan lewat NISN — yang sudah ada diperbarui, yang belum ada dibuat.
    </p>

    <form wire:submit="periksa" class="mk-desk">
        <label for="berkas" class="mk-label">Berkas CSV</label>
        <div class="mk-desk__row">
            <input id="berkas" type="file" wire:model="berkas" accept=".csv,text/csv" class="mk-input mk-input--wide" />
            <x-filament::button type="submit" wire:loading.attr="disabled">Periksa</x-filament::button>
        </div>
        @error('berkas') <p class="mk-error">{{ $message }}</p> @enderror
        @foreach ($galat as $g) <p class="mk-error">{{ $g }}</p> @endforeach
    </form>

    @if ($ringkasan)
        <div class="mk-stat-row">
            <div class="mk-stat">
                <div class="mk-stat__label">Hasil impor</div>
                <div class="mk-desk__name">{{ $ringkasan }}</div>
            </div>
        </div>
        @if (count($akunBaru) > 0)
            <p class="mk-hint">
                Kata sandi siswa baru dibuat acak dan <strong>hanya bisa diunduh sekarang</strong> —
                tidak tersimpan dalam bentuk yang bisa dibaca. Bagikan ke siswa yang bersangkutan.
            </p>
            <div class="mk-desk__row mk-desk">
                <x-filament::button wire:click="unduhAkun" icon="heroicon-o-arrow-down-tray">
                    Unduh Daftar Akun ({{ count($akunBaru) }})
                </x-filament::button>
                <x-filament::button color="gray" wire:click="batal">Selesai</x-filament::button>
            </div>
        @endif
    @endif

    @if (count($baris) > 0)
        <div class="mk-stat-row">
            <div class="mk-stat"><div class="mk-stat__label">Siswa baru</div><div class="mk-stat__value">{{ $this->jumlah('baru') }}</div></div>
            <div class="mk-stat"><div class="mk-stat__label">Diperbarui</div><div class="mk-stat__value">{{ $this->jumlah('perbarui') }}</div></div>
            <div class="mk-stat"><div class="mk-stat__label">Ditolak</div><div class="mk-stat__value">{{ $this->jumlah('ditolak') }}</div></div>
        </div>

        <div class="mk-desk__row mk-desk">
            <x-filament::button wire:click="terapkan"
                wire:confirm="Terapkan {{ $this->jumlah('baru') + $this->jumlah('perbarui') }} baris ke data siswa?"
                :disabled="$this->jumlah('baru') + $this->jumlah('perbarui') === 0">
                Terapkan Impor
            </x-filament::button>
            <x-filament::button color="gray" wire:click="batal">Batal</x-filament::button>
        </div>

        <div class="mk-scroll">
            <table class="mk-matrix">
                <thead>
                    <tr><th>Baris</th><th>NISN</th><th>Nama</th><th>Kelas</th><th>Aktif</th><th>Tindakan</th></tr>
                </thead>
                <tbody>
                    @foreach ($baris as $b)
                        <tr>
                            <td>{{ $b['nomor'] }}</td>
                            <td>{{ $b['nisn'] ?: '—' }}</td>
                            <td class="mk-sticky">{{ $b['nama'] ?: '—' }}</td>
                            <td>{{ $b['kelas'] ?: '—' }}</td>
                            <td>{{ $b['aktif'] ? 'Ya' : 'Tidak' }}</td>
                            <td class="{{ $b['aksi'] === 'ditolak' ? 'mk-kode-a' : '' }}">
                                {{ match ($b['aksi']) { 'baru' => 'Buat baru', 'perbarui' => 'Perbarui', default => 'Ditolak: '.$b['alasan'] } }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
