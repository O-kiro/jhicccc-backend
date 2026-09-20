{{--
    Judul halaman di topbar (render hook TOPBAR_START). Gaya: .mk-topbar-title.

    Judul TIDAK bisa diambil dari $livewire: topbar adalah komponen Livewire
    tersendiri, jadi $livewire di sini merujuk topbar, bukan halaman. Sumber
    yang andal adalah kelas halaman pada route yang sedang aktif.

    Untuk halaman resource, label diambil dari resource-nya — dari halamannya
    hasilnya label Inggris hasil generate ("List Students"), bukan "Siswa".
--}}
@php
    $kelas = request()->route()?->getAction('controller');
    $judul = null;

    if (is_string($kelas) && class_exists($kelas)) {
        try {
            $judul = method_exists($kelas, 'getResource')
                ? $kelas::getResource()::getNavigationLabel()
                : (method_exists($kelas, 'getNavigationLabel') ? $kelas::getNavigationLabel() : null);
        } catch (\Throwable) {
            $judul = null;
        }
    }
@endphp

@if (filled($judul))
    <h1 class="mk-topbar-title">{{ $judul }}</h1>
@endif
