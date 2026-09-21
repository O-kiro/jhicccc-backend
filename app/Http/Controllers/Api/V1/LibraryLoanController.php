<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BookLoanResource;
use App\Http\Resources\V1\BookResource;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use App\Services\Sirkulasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Peminjaman mandiri dari portal siswa.
 *
 * Aturannya — kuota, pinjaman ganda, lama pinjam — sengaja memakai
 * App\Services\Sirkulasi yang sama dengan meja petugas, supaya portal dan
 * perpustakaan tidak pernah berbeda pendapat soal boleh-tidaknya meminjam.
 */
class LibraryLoanController extends Controller
{
    /** Katalog lengkap, dengan penanda buku yang sedang dipinjam siswa ini. */
    public function catalogue(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', Rule::in(array_keys(BookResource::TONES))],
        ]);

        $dipinjam = $student->bookLoans()->active()->pluck('book_id')->all();

        $buku = Book::query()
            ->when($filter['q'] ?? null, fn ($q, $kata) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$kata}%")
                ->orWhere('author', 'like', "%{$kata}%")))
            ->when($filter['kategori'] ?? null, fn ($q, $k) => $q->where('category', $k))
            ->orderBy('title')
            ->get();

        return response()->json([
            'books' => $buku->map(fn (Book $b): array => [
                ...(new BookResource($b))->resolve($request),
                'borrowed_by_me' => in_array($b->id, $dipinjam, true),
            ]),
            'categories' => array_keys(BookResource::TONES),
            'active_loans' => count($dipinjam),
            'loan_quota' => BookLoan::KUOTA,
            'durations' => array_keys(Sirkulasi::DURASI),
        ]);
    }

    public function borrow(Request $request, Book $book, Sirkulasi $sirkulasi): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        // Tanda kurung penting: tanpa itu cast (int) dijalankan sebelum ??
        // sempat memeriksa kuncinya, dan permintaan tanpa `days` jadi galat 500.
        $hari = (int) ($request->validate([
            'days' => ['nullable', 'integer', Rule::in(array_keys(Sirkulasi::DURASI))],
        ])['days'] ?? 14);

        $pinjaman = $sirkulasi->pinjam($student, $book, $hari);

        return response()->json(new BookLoanResource($pinjaman->load('book')), 201);
    }

    public function return(Request $request, BookLoan $loan, Sirkulasi $sirkulasi): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        // 404, bukan 403: keberadaan pinjaman siswa lain tidak perlu dibocorkan.
        if ($loan->student_id !== $student->id) {
            throw new NotFoundHttpException('Pinjaman tidak ditemukan.');
        }

        $sirkulasi->kembalikan($loan);

        return response()->json(['returned' => true]);
    }
}
