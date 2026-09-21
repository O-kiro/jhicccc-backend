<x-filament-panels::page>
    {{-- Penyaring: bulan dan kelas --}}
    <div class="flex flex-wrap items-end gap-4">
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

    @php
        $tanggal = $this->getTanggalList();
        $baris = $this->getBaris();
        $kodeList = \App\Models\Attendance::KODE;
    @endphp

    <p class="mk-hint">
        Kode: @foreach ($kodeList as $k => $arti){{ $k }} {{ $arti }}@if (! $loop->last) &middot; @endif @endforeach
    </p>

    @if ($baris->isEmpty())
        <p class="mk-empty">Belum ada siswa aktif pada filter ini.</p>
    @else
        <div class="mk-scroll">
            <table class="mk-matrix">
                <thead>
                    <tr>
                        <th class="mk-sticky">Siswa</th>
                        <th>Kelas</th>
                        @foreach ($tanggal as $t)
                            <th class="mk-day">{{ $t }}</th>
                        @endforeach
                        @foreach (array_keys($kodeList) as $k)
                            <th class="mk-sum">{{ $k }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($baris as $row)
                        <tr>
                            <td class="mk-sticky">{{ $row['siswa']->name }}</td>
                            <td>{{ $row['siswa']->classroom?->name ?? '—' }}</td>
                            @foreach ($tanggal as $t)
                                @php $kode = $row['kode'][$t] ?? null; @endphp
                                <td class="mk-day mk-kode-{{ $kode ? strtolower($kode) : 'kosong' }}">
                                    {{ $kode ?? '·' }}
                                </td>
                            @endforeach
                            @foreach (array_keys($kodeList) as $k)
                                <td class="mk-sum">{{ $row['rekap'][$k] ?: '' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
