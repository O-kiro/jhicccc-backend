{{--
    Filament v5 mengompilasi CSS-nya sendiri, jadi kelas utilitas Tailwind
    sembarangan (flex, gap-*, dll) belum tentu ada. Tata letak di sini memakai
    gaya inline agar tampil benar tanpa perlu membangun tema kustom.
--}}
<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Modul belum tersedia</x-slot>

        <x-slot name="description">
            Struktur menu ini sudah mengikuti dokumen tur dashboard, tetapi datanya
            belum dibangun. Halaman ini sementara, menunggu spesifikasi lengkap.
        </x-slot>

        <p style="font-size:0.875rem;line-height:1.6;">{{ $summary }}</p>
    </x-filament::section>

    @if (filled($features))
        <x-filament::section>
            <x-slot name="heading">Rencana isi modul</x-slot>

            <ol style="margin:0;padding:0;list-style:none;display:grid;gap:1rem;">
                @foreach ($features as $index => $feature)
                    <li style="display:grid;grid-template-columns:1.75rem 1fr;gap:0.75rem;align-items:start;">
                        <span style="display:inline-grid;place-items:center;width:1.75rem;height:1.75rem;
                                     border-radius:0.5rem;font-size:0.75rem;font-weight:700;
                                     background:rgba(120,120,130,0.12);">
                            {{ $index + 1 }}
                        </span>
                        <span>
                            <strong style="display:block;font-size:0.875rem;font-weight:600;">
                                {{ $feature['title'] }}
                            </strong>
                            <span style="display:block;margin-top:0.125rem;font-size:0.875rem;
                                         line-height:1.6;opacity:0.7;">
                                {{ $feature['description'] }}
                            </span>
                        </span>
                    </li>
                @endforeach
            </ol>
        </x-filament::section>
    @endif
</x-filament-panels::page>
