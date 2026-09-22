<?php

/**
 * Pesan validasi berbahasa Indonesia.
 *
 * APP_LOCALE sudah "id", tetapi tanpa berkas ini Laravel jatuh ke bahasa
 * Inggris — sehingga siswa melihat pesan campur seperti
 * "The judul field must be at least 10 characters."
 *
 * Sengaja hanya memuat aturan yang benar-benar dipakai aplikasi ini, bukan
 * salinan lengkap berkas bawaan Laravel: yang tidak ada di sini tetap jatuh
 * ke bahasa Inggris, dan itu lebih mudah ditemukan daripada terjemahan mati
 * yang tidak pernah dipanggil.
 */
return [
    'after_or_equal' => 'Kolom :attribute paling awal :date.',
    'array' => 'Kolom :attribute harus berupa daftar.',
    'before_or_equal' => 'Kolom :attribute paling akhir :date.',
    'boolean' => 'Kolom :attribute harus bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date_format' => 'Kolom :attribute harus berformat :format.',
    'different' => ':Attribute harus berbeda dari :other.',
    'distinct' => 'Kolom :attribute berisi nilai ganda.',
    'email' => 'Kolom :attribute harus berupa alamat email yang sah.',
    'exists' => ':attribute yang dipilih tidak ada.',
    'in' => ':attribute yang dipilih tidak sah.',
    'integer' => 'Kolom :attribute harus berupa angka bulat.',
    'required' => 'Kolom :attribute wajib diisi.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'url' => 'Kolom :attribute harus berupa tautan yang sah, diawali http:// atau https://.',

    'max' => [
        'array' => 'Kolom :attribute tidak boleh lebih dari :max item.',
        'file' => 'Kolom :attribute tidak boleh lebih dari :max kilobita.',
        'numeric' => 'Kolom :attribute tidak boleh lebih dari :max.',
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],

    // Aturan Password::min()->letters()->numbers().
    'password' => [
        'letters' => ':Attribute harus mengandung setidaknya satu huruf.',
        'numbers' => ':Attribute harus mengandung setidaknya satu angka.',
    ],

    'min' => [
        'array' => 'Kolom :attribute harus berisi minimal :min item.',
        'file' => 'Kolom :attribute harus minimal :min kilobita.',
        'numeric' => 'Kolom :attribute harus minimal :min.',
        'string' => 'Kolom :attribute harus minimal :min karakter.',
    ],

    /**
     * Nama kolom yang tampil di pesan. Ditaruh di sini supaya controller
     * tidak perlu mengulang daftar yang sama.
     */
    'attributes' => [
        'assessed_on' => 'tanggal penilaian',
        'body' => 'isi',
        'choice' => 'pilihan jawaban',
        'current_password' => 'kata sandi saat ini',
        'date' => 'tanggal',
        'days' => 'lama pinjam',
        'flagged' => 'penanda ragu-ragu',
        'forum_category_id' => 'kategori',
        'identifier' => 'NISN, NIP, atau email',
        'kategori' => 'kategori',
        'kelas' => 'kelas',
        'note' => 'catatan',
        'parent_id' => 'balasan yang dibalas',
        'password' => 'kata sandi',
        'present_count' => 'jumlah hadir',
        'question_id' => 'soal',
        'schedule_id' => 'jadwal',
        'scores' => 'nilai',
        'title' => 'judul',
        'topic' => 'materi',
        'url' => 'tautan',
    ],
];
