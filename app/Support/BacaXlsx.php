<?php

namespace App\Support;

use SimpleXMLElement;
use Throwable;
use ZipArchive;

/**
 * Membaca lembar pertama berkas .xlsx menjadi baris-baris teks.
 *
 * Sengaja kecil, tanpa pustaka spreadsheet: cukup untuk tabel ranking yang
 * diunggah wali kelas. Rumus dibaca nilai terakhirnya yang tersimpan, gaya
 * dan sel gabungan diabaikan.
 */
class BacaXlsx
{
    /** Batas aman supaya berkas raksasa tidak membebani respons. */
    public const MAKS_BARIS = 500;

    public const MAKS_KOLOM = 30;

    /** @return list<list<string>>|null null bila berkasnya tidak terbaca */
    public static function lembarPertama(string $jalur): ?array
    {
        try {
            $zip = new ZipArchive;
            if ($zip->open($jalur) !== true) {
                return null;
            }

            $teksBersama = [];
            if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
                foreach (self::xml($xml)->si as $si) {
                    // Teks berformat (rich text) terpecah di beberapa <r><t>.
                    $teksBersama[] = isset($si->t)
                        ? (string) $si->t
                        : implode('', array_map(fn ($r) => (string) $r->t, iterator_to_array($si->r, false)));
                }
            }

            $lembar = $zip->getFromName(self::jalurLembarPertama($zip));
            $zip->close();

            if ($lembar === false) {
                return null;
            }

            $baris = [];
            foreach (self::xml($lembar)->sheetData->row as $row) {
                $sel = [];
                foreach ($row->c as $c) {
                    $kolom = self::indeksKolom((string) $c['r']);
                    if ($kolom >= self::MAKS_KOLOM) {
                        continue;
                    }
                    $jenis = (string) $c['t'];
                    $nilai = match ($jenis) {
                        's' => $teksBersama[(int) $c->v] ?? '',
                        'inlineStr' => (string) ($c->is->t ?? ''),
                        'b' => ((string) $c->v) === '1' ? 'TRUE' : 'FALSE',
                        default => self::angka((string) $c->v),
                    };
                    $sel[$kolom] = trim($nilai);
                }

                if ($sel === [] || implode('', $sel) === '') {
                    continue;
                }

                $lebar = max(array_keys($sel)) + 1;
                $baris[] = array_map(fn ($i) => $sel[$i] ?? '', range(0, $lebar - 1));

                if (count($baris) >= self::MAKS_BARIS) {
                    break;
                }
            }

            // Ratakan lebar supaya tabel di portal tidak bergerigi.
            $lebar = $baris === [] ? 0 : max(array_map('count', $baris));

            return array_map(fn ($b) => array_pad($b, $lebar, ''), $baris);
        } catch (Throwable) {
            return null;
        }
    }

    private static function xml(string $isi): SimpleXMLElement
    {
        // LIBXML_NONET: jangan pernah mengambil apa pun dari jaringan.
        return new SimpleXMLElement($isi, LIBXML_NONET);
    }

    /** Lembar pertama menurut urutan di workbook, bukan nama berkasnya. */
    private static function jalurLembarPertama(ZipArchive $zip): string
    {
        $workbook = $zip->getFromName('xl/workbook.xml');
        $relasi = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbook !== false && $relasi !== false) {
            $wb = self::xml($workbook);
            $wb->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $pertama = $wb->sheets->sheet[0] ?? null;
            $rid = $pertama ? (string) $pertama->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'] : '';

            foreach (self::xml($relasi)->Relationship as $r) {
                if ((string) $r['Id'] === $rid) {
                    $target = ltrim((string) $r['Target'], '/');

                    return str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
                }
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    /** "C12" → 2 */
    private static function indeksKolom(string $ref): int
    {
        $huruf = preg_replace('/\d/', '', $ref);
        $n = 0;
        foreach (str_split($huruf) as $h) {
            $n = $n * 26 + (ord($h) - 64);
        }

        return max(0, $n - 1);
    }

    /** Excel menyimpan 87.5 sebagai "87.5" atau "87.499999999999"; rapikan. */
    private static function angka(string $v): string
    {
        if ($v === '' || ! is_numeric($v)) {
            return $v;
        }

        return (string) round((float) $v, 2);
    }
}
