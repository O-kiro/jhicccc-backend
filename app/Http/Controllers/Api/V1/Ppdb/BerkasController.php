<?php

namespace App\Http\Controllers\Api\V1\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\PpdbDocument;
use App\Models\PpdbRegistrant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Berkas pendaftaran: satu baris per jenis berkas. Mengunggah ulang jenis
 * yang sama menggantikan berkas lama dan mengembalikan statusnya ke
 * "menunggu" — panitia harus memeriksa yang baru.
 */
class BerkasController extends Controller
{
    /** Batas ukuran satu berkas, dalam kilobita. */
    private const MAKS_KB = 5120;

    public function index(Request $request): JsonResponse
    {
        /** @var PpdbRegistrant $pendaftar */
        $pendaftar = $request->user();

        $terunggah = $pendaftar->documents()->get()->keyBy('jenis');
        $wajib = $pendaftar->jenisWajib();

        $berkas = collect(PpdbRegistrant::BERKAS)->map(function (array $b, string $jenis) use ($terunggah, $wajib): array {
            $doc = $terunggah->get($jenis);

            return [
                'jenis' => $jenis,
                'label' => $b['label'],
                'wajib' => in_array($jenis, $wajib, true),
                'document' => $doc ? [
                    'id' => $doc->id,
                    'original_name' => $doc->original_name,
                    'size_kb' => $doc->size_kb,
                    'status' => $doc->status,
                    'status_label' => $doc->labelStatus(),
                    'note' => $doc->note,
                    'file_url' => $doc->fileUrl(),
                    'uploaded_on' => $doc->created_at?->toDateString(),
                ] : null,
            ];
        })->values();

        $kurang = $berkas->filter(fn (array $b): bool => $b['wajib'] && $b['document'] === null)->count();
        $ditolak = $terunggah->where('status', 'ditolak')->count();

        return response()->json([
            'registrant' => [
                'name' => $pendaftar->name,
                'registration_number' => $pendaftar->registration_number,
                'jalur' => $pendaftar->jalur,
                'origin_school' => $pendaftar->origin_school,
                'note' => $pendaftar->note,
            ],
            'documents' => $berkas,
            'summary' => [
                'wajib' => count($wajib),
                'terunggah' => $terunggah->count(),
                'kurang' => $kurang,
                'ditolak' => $ditolak,
                'diterima' => $terunggah->where('status', 'diterima')->count(),
                'lengkap' => $kurang === 0 && $ditolak === 0,
            ],
            'max_file_kb' => self::MAKS_KB,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var PpdbRegistrant $pendaftar */
        $pendaftar = $request->user();

        $data = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(PpdbRegistrant::BERKAS))],
            // PDF saja, sesuai ketentuan panitia di halaman PPDB.
            'file' => ['required', 'file', 'mimes:pdf', 'max:'.self::MAKS_KB],
        ], [
            'file.mimes' => 'Berkas harus berformat PDF.',
            'file.max' => 'Berkas terlalu besar. Maksimal 5 MB.',
        ]);

        $berkas = $data['file'];

        // Berkas lama dihapus lewat MembersihkanGambarUnggahan saat kolomnya
        // berubah; statusnya kembali menunggu karena isinya baru.
        $doc = PpdbDocument::query()->updateOrCreate(
            ['ppdb_registrant_id' => $pendaftar->id, 'jenis' => $data['jenis']],
            [
                'file_path' => $berkas->store('ppdb/'.$pendaftar->id, 'public'),
                'original_name' => $berkas->getClientOriginalName(),
                'size_kb' => (int) ceil($berkas->getSize() / 1024),
                'status' => 'menunggu',
                'note' => null,
                'verified_at' => null,
                'verified_by' => null,
            ],
        );

        return response()->json([
            'id' => $doc->id,
            'status' => $doc->status,
            'file_url' => $doc->fileUrl(),
        ], 201);
    }

    public function destroy(Request $request, PpdbDocument $document): JsonResponse
    {
        /** @var PpdbRegistrant $pendaftar */
        $pendaftar = $request->user();

        // 404, bukan 403: berkas pendaftar lain tidak perlu terlihat ada.
        if ($document->ppdb_registrant_id !== $pendaftar->id) {
            throw new NotFoundHttpException('Berkas tidak ditemukan.');
        }

        if ($document->status === 'diterima') {
            return response()->json([
                'message' => 'Berkas yang sudah diterima panitia tidak bisa dihapus.',
            ], 422);
        }

        $document->delete();

        return response()->json(['deleted' => true]);
    }
}
