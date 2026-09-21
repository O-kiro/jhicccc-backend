<?php

namespace Database\Factories;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    private static int $urutan = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = fake()->randomElement(['X', 'XI', 'XII']);

        // Kolom name punya batasan unik. Huruf acak sempat bentrok, dan
        // fake()->unique() kehabisan setelah 26 kelas — tes yang membuat
        // puluhan siswa sekaligus langsung gagal. Urutan ini tidak terbatas.
        self::$urutan++;

        return [
            'name' => $level.'-'.self::$urutan,
            'level' => $level,
            'academic_year' => '2025/2026',
            'homeroom_teacher_id' => null,
        ];
    }
}
