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

class LibraryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $categories = Book::query()
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->category,
                'count' => $row->total.' judul',
                'icon' => BookResource::TONES[$row->category]['icon'] ?? 'book',
                'tone' => BookResource::TONES[$row->category]['tone'] ?? 'teal',
            ]);

        $loans = $student->bookLoans()->active()->with('book')->orderBy('due_on')->get();

        // "Lanjutkan Membaca" = pinjaman yang paling jauh progresnya.
        $reading = $loans->filter(fn ($l): bool => $l->current_page > 0)
            ->sortByDesc('current_page')
            ->first();

        return response()->json([
            'categories' => $categories,
            'new_arrivals' => BookResource::collection(
                Book::query()->latest('id')->limit(4)->get(),
            ),
            'loans' => BookLoanResource::collection($loans),
            'loan_quota' => BookLoan::KUOTA,
            'loan_durations' => array_keys(Sirkulasi::DURASI),
            'continue_reading' => $reading ? new BookLoanResource($reading) : null,
        ]);
    }
}
