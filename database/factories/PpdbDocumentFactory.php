<?php

namespace Database\Factories;

use App\Models\PpdbDocument;
use App\Models\PpdbRegistrant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PpdbDocument>
 */
class PpdbDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ppdb_registrant_id' => PpdbRegistrant::factory(),
            'jenis' => 'kartu_keluarga',
            'file_path' => 'ppdb/contoh.pdf',
            'original_name' => 'kk.pdf',
            'size_kb' => 64,
            'status' => 'menunggu',
            'note' => null,
        ];
    }
}
