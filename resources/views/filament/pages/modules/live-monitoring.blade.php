<x-filament-panels::page>
    <p class="mk-hint">Data per {{ now()->translatedFormat('l, j F Y — H:i') }}.</p>

    <h2 class="mk-section-title">Ringkasan Hari Ini</h2>
    <div class="mk-stat-row">
        @foreach ($this->getRingkasan() as $label => $nilai)
            <div class="mk-stat">
                <div class="mk-stat__label">{{ $label }}</div>
                <div class="mk-stat__value">{{ $nilai }}</div>
            </div>
        @endforeach
    </div>

    <h2 class="mk-section-title">Sumber Absen</h2>
    <div class="mk-stat-row">
        @foreach ($this->getSumber() as $label => $nilai)
            <div class="mk-stat">
                <div class="mk-stat__label">{{ $label }}</div>
                <div class="mk-stat__value">{{ $nilai }}</div>
            </div>
        @endforeach
    </div>

    <h2 class="mk-section-title">Sedang Berada di Luar</h2>
    @php $diLuar = $this->getDiLuar(); @endphp
    @if ($diLuar->isEmpty())
        <p class="mk-empty">Tidak ada siswa yang tercatat keluar dan belum kembali.</p>
    @else
        <div class="mk-scroll">
            <table class="mk-matrix">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Keluar</th>
                        <th>Keperluan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($diLuar as $izin)
                        <tr>
                            <td class="mk-sticky">{{ $izin->student?->name ?? '—' }}</td>
                            <td>{{ $izin->student?->classroom?->name ?? '—' }}</td>
                            <td>{{ $izin->left_at?->format('H:i') }}</td>
                            <td>{{ $izin->reason }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
