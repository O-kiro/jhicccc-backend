{{--
    Dasbor admin — komposisi.

    Tiap bagian ada di berkasnya sendiri di dasbor/, dan seluruh gaya berasal
    dari kelas .mk-* di resources/css/filament/admin/theme.css. Tidak ada gaya
    inline: untuk mengubah warna, ukuran, atau jarak, ubah tokennya di sana.

        dasbor/hero.blade.php         panel sapaan
        dasbor/ringkasan.blade.php    tiga kartu ringkasan
        dasbor/jadwal.blade.php       jadwal hari ini
        dasbor/pengumuman.blade.php   pengumuman terbaru
--}}
<div class="mk-dash">
    @include('filament.widgets.dasbor.hero')

    @include('filament.widgets.dasbor.ringkasan')

    <div class="mk-columns">
        @include('filament.widgets.dasbor.jadwal')

        @include('filament.widgets.dasbor.pengumuman')
    </div>
</div>
