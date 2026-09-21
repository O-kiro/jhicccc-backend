{{--
    Rapor Digital Madrasah — versi cetak.

    Gaya ditulis inline di berkas ini, bukan memakai tema Tailwind panel admin:
    dompdf hanya memahami sebagian kecil CSS dan tidak menjalankan JavaScript,
    jadi lembar gaya aplikasi tidak bisa dipakai di sini.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rapor {{ $student->name }}</title>
    <style>
        @page { margin: 28mm 18mm; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            line-height: 1.55;
            color: #2c2c2a;
            margin: 0;
        }

        .kop {
            border-bottom: 2px solid #0f6e56;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .kop h1 {
            font-size: 17px;
            color: #0f6e56;
            margin: 0 0 2px;
            letter-spacing: .3px;
        }
        .kop p { margin: 0; font-size: 10px; color: #5a5a56; }

        h2 {
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #0f6e56;
            margin: 20px 0 8px;
            border-bottom: 1px solid #e4e1d8;
            padding-bottom: 4px;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; text-align: left; vertical-align: top; }

        .identitas td { padding: 3px 0; }
        .identitas td:first-child { width: 130px; color: #5a5a56; }

        .nilai th {
            background: #f1efe8;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #5a5a56;
            border-bottom: 1px solid #ddd9cd;
        }
        .nilai td { border-bottom: 1px solid #eeebe2; }
        .nilai .angka { text-align: right; width: 70px; font-weight: bold; }

        .ringkas td {
            width: 25%;
            background: #f1efe8;
            border: 3px solid #fff;
            text-align: center;
        }
        .ringkas .label { font-size: 9px; text-transform: uppercase; color: #5a5a56; letter-spacing: .4px; }
        .ringkas .nilai-besar { font-size: 19px; font-weight: bold; color: #0f6e56; }

        .catatan {
            border-left: 3px solid #ba7517;
            padding: 2px 0 2px 10px;
            margin-bottom: 11px;
        }
        .catatan .penulis { font-weight: bold; }
        .catatan .peran { color: #5a5a56; font-size: 9.5px; }

        .kosong { color: #8a8a84; font-style: italic; }

        .kaki {
            position: fixed;
            bottom: -18mm;
            left: 0;
            right: 0;
            font-size: 8.5px;
            color: #8a8a84;
            border-top: 1px solid #e4e1d8;
            padding-top: 5px;
        }
        .kaki .kanan { float: right; }
    </style>
</head>
<body>
    <div class="kop">
        <h1>MAN Kota Batu</h1>
        <p>Rapor Digital Madrasah &middot; Semester {{ $reportCard->semester }} &middot; Tahun Pelajaran {{ $reportCard->academic_year }}</p>
    </div>

    <table class="identitas">
        <tr><td>Nama</td><td><strong>{{ $student->name }}</strong></td></tr>
        <tr><td>NISN</td><td>{{ $student->nisn }}</td></tr>
        <tr><td>Kelas</td><td>{{ $student->classroom?->name ?? '—' }}</td></tr>
    </table>

    <h2>Ringkasan</h2>
    <table class="ringkas">
        <tr>
            <td>
                <div class="label">Rata-rata</div>
                <div class="nilai-besar">{{ number_format((float) $reportCard->average_score, 1) }}</div>
            </td>
            <td>
                <div class="label">Kehadiran</div>
                <div class="nilai-besar">{{ number_format((float) $reportCard->attendance_percentage, 1) }}%</div>
            </td>
            <td>
                <div class="label">Peringkat</div>
                <div class="nilai-besar">
                    {{-- Peringkat boleh kosong: sebagian madrasah tidak merangking. --}}
                    {{ $reportCard->class_rank ? $reportCard->class_rank : '—' }}
                </div>
            </td>
            <td>
                <div class="label">Jumlah Siswa</div>
                <div class="nilai-besar">{{ $reportCard->class_size ?? '—' }}</div>
            </td>
        </tr>
    </table>

    <h2>Daftar Nilai</h2>
    @if ($assessments->isEmpty())
        <p class="kosong">Belum ada penilaian yang tercatat pada periode ini.</p>
    @else
        <table class="nilai">
            <thead>
                <tr>
                    <th>Mata Pelajaran</th>
                    <th>Penilaian</th>
                    <th>Tanggal</th>
                    <th class="angka">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($assessments as $a)
                    <tr>
                        <td>{{ $a->subject?->name ?? '—' }}</td>
                        <td>{{ $a->title ?? '—' }}</td>
                        <td>{{ $a->assessed_on?->translatedFormat('j M Y') ?? '—' }}</td>
                        <td class="angka">{{ $a->score }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Catatan Guru</h2>
    @if ($feedback->isEmpty())
        <p class="kosong">Belum ada catatan dari guru.</p>
    @else
        @foreach ($feedback as $f)
            <div class="catatan">
                <div>
                    <span class="penulis">{{ $f->teacher?->name ?? 'Guru' }}</span>
                    <span class="peran">&middot; {{ $f->role }}</span>
                </div>
                <div>{{ $f->body }}</div>
            </div>
        @endforeach
    @endif

    <div class="kaki">
        Dokumen ini dihasilkan otomatis oleh sistem dan sah tanpa tanda tangan basah.
        <span class="kanan">Dicetak {{ now()->translatedFormat('j F Y, H:i') }}</span>
    </div>
</body>
</html>
