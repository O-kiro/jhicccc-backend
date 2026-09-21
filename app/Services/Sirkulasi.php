<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use Illuminate\Validation\ValidationException;

/**
 * Aturan meja sirkulasi perpustakaan.
 *
 * Dipisah dari halaman panel supaya aturannya — kuota, pinjaman ganda,
 * siswa nonaktif — bisa diuji tanpa antarmuka, dan kelak dipakai ulang oleh
 * kios mandiri atau API tanpa disalin.
 */
class Sirkulasi
{
    /** Pilihan lama pinjam, dalam hari. */
    public const DURASI = [7 => '7 hari', 14 => '14 hari', 21 => '21 hari'];

    /**
     * Mencari siswa dari NISN yang dipindai. Pembaca kartu kadang menyisipkan
     * spasi atau baris baru di ujung, jadi masukannya dirapikan dulu.
     */
    public function siswa(string $nisn): Student
    {
        $nisn = trim($nisn);

        $siswa = Student::query()->where('nisn', $nisn)->first();

        if (! $siswa) {
            throw ValidationException::withMessages(['nisn' => "NISN {$nisn} tidak terdaftar."]);
        }

        if (! $siswa->is_active) {
            throw ValidationException::withMessages(['nisn' => "Akun {$siswa->name} nonaktif; peminjaman ditolak."]);
        }

        return $siswa;
    }

    /** Mencari buku dari kode yang dipindai, atau dari ID bila dipilih manual. */
    public function buku(string|int $kodeAtauId): Book
    {
        $kunci = trim((string) $kodeAtauId);

        $buku = Book::query()->where('code', $kunci)->first()
            ?? (ctype_digit($kunci) ? Book::query()->find((int) $kunci) : null);

        if (! $buku) {
            throw ValidationException::withMessages(['buku' => "Buku dengan kode {$kunci} tidak ditemukan."]);
        }

        return $buku;
    }

    public function pinjam(Student $siswa, Book $buku, int $hari): BookLoan
    {
        if (! array_key_exists($hari, self::DURASI)) {
            throw ValidationException::withMessages(['durasi' => 'Lama pinjam tidak dikenali.']);
        }

        $aktif = $siswa->bookLoans()->active()->get();

        if ($aktif->contains('book_id', $buku->id)) {
            throw ValidationException::withMessages([
                'buku' => "{$siswa->name} masih meminjam \"{$buku->title}\".",
            ]);
        }

        if ($aktif->count() >= BookLoan::KUOTA) {
            throw ValidationException::withMessages([
                'buku' => "{$siswa->name} sudah meminjam ".BookLoan::KUOTA.' buku — batas kuota tercapai.',
            ]);
        }

        return BookLoan::query()->create([
            'book_id' => $buku->id,
            'student_id' => $siswa->id,
            'due_on' => now()->addDays($hari)->toDateString(),
            'current_page' => 0,
        ]);
    }

    public function kembalikan(BookLoan $pinjaman): BookLoan
    {
        if ($pinjaman->returned_at !== null) {
            throw ValidationException::withMessages(['pinjaman' => 'Buku ini sudah dikembalikan sebelumnya.']);
        }

        $pinjaman->update(['returned_at' => now()]);

        return $pinjaman;
    }
}
