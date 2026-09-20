{{--
    Kartu pengguna di dasar sidebar (render hook SIDEBAR_FOOTER).
    Gaya: .mk-nav-user* di resources/css/filament/admin/theme.css.
--}}
@php
    $pengguna = \Filament\Facades\Filament::auth()->user();
@endphp

@if ($pengguna)
    <div class="mk-nav-user">
        <div class="mk-nav-user__card">
            <span class="mk-nav-user__avatar" aria-hidden="true">
                {{ mb_strtoupper(mb_substr($pengguna->name, 0, 1)) }}
            </span>

            <span class="mk-nav-user__text">
                <span class="mk-nav-user__name">{{ $pengguna->name }}</span>
                <span class="mk-nav-user__role">Administrator</span>
            </span>
        </div>
    </div>
@endif
