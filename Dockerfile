# Backend MAN Kota Batu — Laravel 13 + Filament.
#
# PHP 8.5 dipilih agar sama persis dengan versi yang dipakai di mesin
# pengembang saat ini; composer.json sendiri menerima ^8.3.
#
# Node ikut dipasang di image yang sama karena tema admin Filament harus
# dibangun dengan Vite (`npm run build`). Tanpa langkah itu panel admin
# tampil tanpa gaya sama sekali — jebakan yang entrypoint di bawah tangani
# secara otomatis.
FROM php:8.5-cli-alpine

RUN apk add --no-cache git unzip nodejs npm

# Ekstensi PHP dipasang lewat install-php-extensions, bukan
# docker-php-ext-install: yang terakhir gagal saat beberapa ekstensi dibangun
# sekaligus ("cp: can't stat 'modules/*'"), dan mengharuskan header seperti
# icu-dev / sqlite-dev diurus manual. Utilitas ini menanganinya sendiri lalu
# membersihkan build deps.
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl zip bcmath pdo_sqlite opcache

# Batas unggahan bawaan PHP hanya 2 MB, padahal berkas RDM (PDF/Excel) dan
# bukti foto jurnal bisa lebih besar. Tanpa ini unggahan gagal tanpa pesan
# yang jelas: PHP membuang berkasnya sebelum Laravel sempat memvalidasi.
RUN printf 'upload_max_filesize=20M\npost_max_size=25M\n' \
    > /usr/local/etc/php/conf.d/unggahan.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Entrypoint ditulis langsung di sini agar tidak menambah folder baru di repo.
RUN <<'SH' cat > /usr/local/bin/entrypoint
#!/bin/sh
# Menyiapkan apa pun yang belum ada, lalu menjalankan server.
# Semua langkah idempoten — aman dijalankan berulang.
set -e
cd /app

if [ ! -f .env ]; then
    echo "→ .env belum ada, menyalin dari .env.example"
    cp .env.example .env
fi

# Juga dipasang ulang saat composer.lock lebih baru daripada vendor. Tanpa
# perbandingan waktu itu, menarik commit yang menambah dependensi tidak
# berpengaruh apa pun di mesin yang vendor-nya sudah terisi — dan aplikasinya
# gagal dengan "Class not found" yang membingungkan.
if [ ! -f vendor/autoload.php ] || [ composer.lock -nt vendor/autoload.php ]; then
    echo "→ memasang dependensi PHP"
    composer install --no-interaction --prefer-dist
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    echo "→ membuat APP_KEY"
    php artisan key:generate --force
fi

# Memeriksa ISI, bukan sekadar keberadaan folder: volume bernama membuat
# node_modules sudah ada tapi kosong, sehingga `[ ! -d node_modules ]`
# bernilai salah dan npm ci terlewat — lalu `npm run build` gagal dengan
# "vite: not found".
if [ -z "$(ls -A node_modules 2>/dev/null)" ] || [ package-lock.json -nt node_modules ]; then
    echo "→ memasang dependensi Node"
    npm ci
    # Tema ikut dibangun ulang: dependensi berubah berarti hasil build lama
    # belum tentu cocok.
    rm -rf public/build
fi

# Tema Filament wajib dibangun; tanpa ini panel admin tampil tanpa gaya sama
# sekali — gejalanya khas: halaman polos dengan logo raksasa di tengah.
#
# Keberadaan manifest.json saja tidak cukup jadi penanda. Ada dua keadaan yang
# dulu lolos dan membuat tema tidak pernah dibangun ulang:
#   1. manifest.json ada, tapi berkas yang dirujuknya hilang (build terputus,
#      folder assets terhapus sebagian).
#   2. theme.css berubah setelah git pull, tapi manifest lama masih ada.
aset_rusak() {
    [ -f public/build/manifest.json ] || return 0

    php -r '
        $manifest = json_decode(@file_get_contents("public/build/manifest.json"), true);
        if (! is_array($manifest) || $manifest === []) { exit(0); }
        foreach ($manifest as $entry) {
            $berkas = array_merge([$entry["file"] ?? null], $entry["css"] ?? []);
            foreach (array_filter($berkas) as $f) {
                if (! file_exists("public/build/".$f)) { exit(0); }
            }
        }
        exit(1);
    '
}

if aset_rusak || [ -n "$(find resources/css resources/js vite.config.js package.json \
        -newer public/build/manifest.json 2>/dev/null | head -1)" ]; then
    echo "→ membangun aset (tema admin)"
    npm run build
fi

DB=database/database.sqlite
FRESH=0
if [ ! -f "$DB" ]; then
    echo "→ membuat basis data SQLite"
    touch "$DB"
    FRESH=1
fi

php artisan migrate --force

if [ "$FRESH" = "1" ]; then
    echo "→ mengisi data contoh"
    php artisan db:seed --force
fi

# Gambar unggahan CMS disajikan dari public/storage. Tautannya diabaikan Git,
# jadi dibuat di sini — dan harus RELATIF: `artisan storage:link` membuat
# tautan absolut ke jalur host (/Users/...) yang tidak ada di dalam container
# maupun di mesin lain. Opsi --relative-nya butuh paket symfony/filesystem,
# jadi dipakai ln biasa.
if [ ! -e public/storage ]; then
    ln -s ../storage/app/public public/storage
fi

php artisan optimize:clear > /dev/null 2>&1 || true

echo "→ siap di http://localhost:8000  (admin: /admin)"
exec "$@"
SH

# Git di Windows mengubah akhir baris jadi CRLF saat checkout, dan skrip di
# atas ikut terbawa. Shebang-nya lalu terbaca "#!/bin/sh\r", sehingga kernel
# mencari penafsir bernama "/bin/sh\r" dan gagal dengan pesan menyesatkan:
#   exec /usr/local/bin/entrypoint: no such file or directory
# CR dibuang di sini supaya image tetap jalan walau checkout-nya CRLF.
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint \
    && chmod +x /usr/local/bin/entrypoint

EXPOSE 8000

ENTRYPOINT ["entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
