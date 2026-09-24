<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\ReportUpload;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportUpload>
 */
class ReportUploadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'classroom_id' => Classroom::factory(),
            'academic_year' => '2026/2027',
            'semester' => 'Ganjil',
            'file_path' => 'guru/rdm/contoh.pdf',
            'original_name' => 'rapor.pdf',
            'size_kb' => 128,
            'note' => null,
        ];
    }
}
