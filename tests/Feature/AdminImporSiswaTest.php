<?php

namespace Tests\Feature;

use App\Filament\Pages\Modules\SyncData;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Services\ImporSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminImporSiswaTest extends TestCase
{
    use RefreshDatabase;

    private ImporSiswa $impor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->impor = new ImporSiswa;
    }

    public function test_new_and_existing_students_are_told_apart(): void
    {
        Student::factory()->create(['nisn' => '1001']);

        $hasil = $this->impor->periksa("nisn,nama\n1001,Lama\n1002,Baru\n");

        $this->assertSame(['perbarui', 'baru'], array_column($hasil['baris'], 'aksi'));
    }

    /** Excel berlokal Indonesia memakai titik koma dan menyisipkan BOM. */
    public function test_semicolon_files_with_a_bom_are_read(): void
    {
        $hasil = $this->impor->periksa("\u{FEFF}NISN;Nama Lengkap\n2001;Siti\n");

        $this->assertSame([], $hasil['galat']);
        $this->assertSame('Siti', $hasil['baris'][0]['nama']);
        $this->assertSame('baru', $hasil['baris'][0]['aksi']);
    }

    public function test_missing_required_columns_are_reported(): void
    {
        $hasil = $this->impor->periksa("id,alamat\n1,Batu\n");

        $this->assertNotEmpty($hasil['galat']);
        $this->assertSame([], $hasil['baris']);
    }

    public function test_bad_rows_are_rejected_with_a_reason(): void
    {
        Classroom::factory()->create(['name' => 'X-A']);

        $hasil = $this->impor->periksa(implode("\n", [
            'nisn,nama,kelas',
            ',Tanpa NISN,X-A',
            '3001,,X-A',
            '3002,Kelas Salah,X-Z',
            '3003,Asli,X-A',
            '3003,Kembar,X-A',
        ]));

        $aksi = array_column($hasil['baris'], 'aksi');
        $this->assertSame(['ditolak', 'ditolak', 'ditolak', 'baru', 'ditolak'], $aksi);
        $this->assertStringContainsString('X-Z', $hasil['baris'][2]['alasan']);
        $this->assertStringContainsString('kembar', $hasil['baris'][4]['alasan']);
    }

    public function test_applying_creates_students_with_random_passwords(): void
    {
        $hasil = $this->impor->terapkan($this->impor->periksa("nisn,nama\n4001,Baru\n")['baris']);

        $this->assertSame(1, $hasil['baru']);
        $siswa = Student::query()->where('nisn', '4001')->sole();
        $sandi = $hasil['akun'][0]['kata_sandi'];

        $this->assertTrue(Hash::check($sandi, $siswa->password));
        // Sandi tidak boleh sama dengan NISN — itu bisa ditebak siapa saja.
        $this->assertNotSame('4001', $sandi);
    }

    /** Memperbarui siswa lama tidak boleh mengganti kata sandinya. */
    public function test_updating_keeps_the_existing_password(): void
    {
        $lama = Student::factory()->create(['nisn' => '5001', 'name' => 'Nama Lama', 'password' => 'rahasia-lama']);

        $hasil = $this->impor->terapkan($this->impor->periksa("nisn,nama,aktif\n5001,Nama Baru,tidak\n")['baris']);

        $lama->refresh();
        $this->assertSame(1, $hasil['diperbarui']);
        $this->assertSame([], $hasil['akun']);
        $this->assertSame('Nama Baru', $lama->name);
        $this->assertFalse($lama->is_active);
        $this->assertTrue(Hash::check('rahasia-lama', $lama->password));
    }

    public function test_rejected_rows_are_never_written(): void
    {
        $this->impor->terapkan($this->impor->periksa("nisn,nama\n,Tanpa NISN\n")['baris']);

        $this->assertSame(0, Student::query()->count());
    }

    public function test_the_page_runs_check_then_apply(): void
    {
        $berkas = UploadedFile::fake()->createWithContent('siswa.csv', "nisn,nama\n6001,Dari Halaman\n");

        Livewire::test(SyncData::class)
            ->set('berkas', $berkas)
            ->call('periksa')
            ->assertHasNoErrors()
            ->assertSee('Dari Halaman')
            ->call('terapkan')
            ->assertSee('1 siswa baru');

        $this->assertSame(1, Student::query()->where('nisn', '6001')->count());
    }
}
