{{--
    Brand panel: logo + nama madrasah + keterangan panel.
    Menyalin susunan puncak sidebar portal siswa (logo 36px, nama tebal,
    keterangan kecil di bawahnya). Gaya: .mk-brand* di theme.css.

    Dipasang lewat ->brandLogo() di AdminPanelProvider, sehingga menggantikan
    teks brand bawaan Filament di mana pun brand itu dirender.
--}}
<span class="mk-brand">
    <img src="{{ asset('images/logo.png') }}" alt="" class="mk-brand__logo" width="36" height="36">

    <span class="mk-brand__text">
        <span class="mk-brand__name">MAN Kota Batu</span>
        <span class="mk-brand__sub">Panel Admin</span>
    </span>
</span>
