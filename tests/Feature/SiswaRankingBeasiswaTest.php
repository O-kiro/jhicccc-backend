<?php

namespace Tests\Feature;

use App\Models\ReportUpload;
use App\Models\Schedule;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class SiswaRankingBeasiswaTest extends TestCase
{
    use RefreshDatabase;

    /** .xlsx minimal: satu lembar, teks lewat sharedStrings, angka langsung. */
    private function xlsx(): string
    {
        $jalur = tempnam(sys_get_temp_dir(), 'rank').'.xlsx';
        $zip = new ZipArchive;
        $zip->open($jalur, ZipArchive::CREATE);
        $zip->addFromString('xl/workbook.xml', '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Ranking" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/sharedStrings.xml', '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><si><t>No</t></si><si><t>Nama</t></si><si><t>Nilai</t></si><si><t>Aisyah</t></si></sst>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'
            .'<row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c><c r="C1" t="s"><v>2</v></c></row>'
            .'<row r="2"><c r="A2"><v>1</v></c><c r="B2" t="s"><v>3</v></c><c r="C2"><v>91.499999999</v></c></row>'
            .'</sheetData></worksheet>');
        $zip->close();

        return $jalur;
    }

    public function test_excel_yang_ditandai_guru_tampil_sebagai_ranking_kelas(): void
    {
        Storage::fake('public');
        $guru = Teacher::factory()->create();
        $siswa = Student::factory()->create();
        Storage::disk('public')->put('guru/rdm/rank.xlsx', file_get_contents($this->xlsx()));

        ReportUpload::factory()->for($guru)->create([
            'classroom_id' => $siswa->classroom_id,
            'file_path' => 'guru/rdm/rank.xlsx',
            'shown_to_students' => true,
        ]);
        // Berkas lain yang tidak ditandai tidak ikut tampil.
        ReportUpload::factory()->create(['classroom_id' => $siswa->classroom_id]);
        TeacherFeedback::query()->create([
            'student_id' => $siswa->id, 'teacher_id' => $guru->id, 'role' => 'Wali Kelas', 'body' => 'Pertahankan!',
        ]);

        $this->actingAs($siswa, 'student')->getJson(route('api.v1.ranking'))
            ->assertOk()
            ->assertJsonPath('sheet.rows', [['No', 'Nama', 'Nilai'], ['1', 'Aisyah', '91.5']])
            ->assertJsonPath('sheet.teacher', $guru->name)
            ->assertJsonPath('teacher_feedback.0.body', 'Pertahankan!');
    }

    public function test_tanpa_excel_ranking_sheet_null(): void
    {
        $this->actingAs(Student::factory()->create(), 'student')->getJson(route('api.v1.ranking'))
            ->assertOk()
            ->assertJsonPath('sheet', null);
    }

    public function test_guru_menandai_excel_untuk_siswa_dan_pdf_ditolak(): void
    {
        Storage::fake('public');
        $guru = Teacher::factory()->create();
        $siswa = Student::factory()->create();
        Schedule::factory()->for($guru)->create(['classroom_id' => $siswa->classroom_id]);

        $kirim = fn (UploadedFile $f) => $this->actingAs($guru, 'teacher')->post(route('api.v1.guru.rdm.store'), [
            'classroom_id' => $siswa->classroom_id,
            'academic_year' => '2026/2027',
            'semester' => 'Ganjil',
            'file' => $f,
            'shown_to_students' => '1',
        ], ['Accept' => 'application/json']);

        $kirim(UploadedFile::fake()->create('rapor.pdf', 10, 'application/pdf'))->assertUnprocessable();
        $kirim(new UploadedFile($this->xlsx(), 'ranking.xlsx', null, null, true))->assertCreated();

        $this->assertDatabaseHas('report_uploads', ['original_name' => 'ranking.xlsx', 'shown_to_students' => true]);
    }

    public function test_siswa_bisa_membuka_katalog_beasiswa(): void
    {
        Scholarship::factory()->count(2)->create();

        $this->actingAs(Student::factory()->create(), 'student')->getJson(route('api.v1.beasiswa'))
            ->assertOk()
            ->assertJsonPath('summary.program_total', 2);
    }
}
