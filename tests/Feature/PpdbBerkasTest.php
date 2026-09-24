<?php

namespace Tests\Feature;

use App\Filament\Resources\PpdbRegistrants\Pages\EditPpdbRegistrant;
use App\Filament\Resources\PpdbRegistrants\Pages\ListPpdbRegistrants;
use App\Filament\Resources\PpdbRegistrants\RelationManagers\DocumentsRelationManager;
use App\Models\PpdbDocument;
use App\Models\PpdbRegistrant;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PpdbSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Unggah berkas PPDB: akun dibuat panitia, pendaftar mengunggah PDF, panitia
 * memverifikasi.
 */
class PpdbBerkasTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_registrant_logs_in_with_their_registration_number(): void
    {
        $pendaftar = PpdbRegistrant::factory()->create([
            'registration_number' => 'PPDB26-0009',
            'password' => 'rahasia123',
            'jalur' => 'Prestasi',
        ]);

        $res = $this->postJson(route('api.v1.ppdb.login'), [
            'registration_number' => 'ppdb26-0009',
            'password' => 'rahasia123',
        ])->assertOk()
            ->assertJsonPath('role', 'ppdb')
            ->assertJsonPath('registrant.jalur', 'Prestasi');

        $this->withToken($res->json('token'))
            ->getJson(route('api.v1.ppdb.berkas.index'))
            ->assertOk()
            ->assertJsonPath('registrant.registration_number', 'PPDB26-0009');

        $this->assertSame(1, $pendaftar->tokens()->count());
    }

    public function test_login_is_refused_without_a_password_or_when_inactive(): void
    {
        PpdbRegistrant::factory()->tanpaAksesPortal()->create(['registration_number' => 'PPDB26-1000']);
        PpdbRegistrant::factory()->inactive()->create([
            'registration_number' => 'PPDB26-1001',
            'password' => 'rahasia123',
        ]);

        $this->postJson(route('api.v1.ppdb.login'), [
            'registration_number' => 'PPDB26-1000',
            'password' => 'apa-saja',
        ])->assertStatus(422)
            ->assertJsonPath('errors.registration_number.0', 'Nomor pendaftaran atau kata sandi salah.');

        $this->postJson(route('api.v1.ppdb.login'), [
            'registration_number' => 'PPDB26-1001',
            'password' => 'rahasia123',
        ])->assertStatus(422)
            ->assertJsonPath('errors.registration_number.0', 'Akun ini tidak aktif. Hubungi panitia PPDB.');
    }

    public function test_the_checklist_marks_required_documents_by_jalur(): void
    {
        $prestasi = PpdbRegistrant::factory()->create(['jalur' => 'Prestasi']);
        $reguler = PpdbRegistrant::factory()->create(['jalur' => 'Reguler 1']);

        $wajib = fn (PpdbRegistrant $p) => collect(
            $this->actingAs($p, 'ppdb')->getJson(route('api.v1.ppdb.berkas.index'))->json('documents'),
        )->where('wajib', true)->pluck('jenis')->all();

        $this->assertContains('sertifikat', $wajib($prestasi));
        $this->app['auth']->forgetGuards();
        $this->assertNotContains('sertifikat', $wajib($reguler));
    }

    public function test_uploading_a_document_replaces_the_previous_one_and_resets_verification(): void
    {
        Storage::fake('public');
        $pendaftar = PpdbRegistrant::factory()->create();

        $pertama = $this->actingAs($pendaftar, 'ppdb')
            ->postJson(route('api.v1.ppdb.berkas.store'), [
                'jenis' => 'kartu_keluarga',
                'file' => UploadedFile::fake()->create('kk-lama.pdf', 100, 'application/pdf'),
            ])->assertCreated()->json('id');

        PpdbDocument::query()->find($pertama)->update(['status' => 'diterima']);
        $jalurLama = PpdbDocument::query()->find($pertama)->file_path;

        $kedua = $this->actingAs($pendaftar, 'ppdb')
            ->postJson(route('api.v1.ppdb.berkas.store'), [
                'jenis' => 'kartu_keluarga',
                'file' => UploadedFile::fake()->create('kk-baru.pdf', 120, 'application/pdf'),
            ])->assertCreated()
            ->assertJsonPath('status', 'menunggu')
            ->json('id');

        // Satu jenis tetap satu baris, dan berkas lamanya dibuang.
        $this->assertSame($pertama, $kedua);
        $this->assertSame(1, PpdbDocument::query()->count());
        $this->assertSame('kk-baru.pdf', PpdbDocument::query()->sole()->original_name);
        Storage::disk('public')->assertMissing($jalurLama);
    }

    public function test_only_pdf_files_are_accepted(): void
    {
        Storage::fake('public');
        $pendaftar = PpdbRegistrant::factory()->create();

        $this->actingAs($pendaftar, 'ppdb')
            ->postJson(route('api.v1.ppdb.berkas.store'), [
                'jenis' => 'foto',
                'file' => UploadedFile::fake()->image('foto.jpg'),
            ])->assertStatus(422)
            ->assertJsonPath('errors.file.0', 'Berkas harus berformat PDF.');
    }

    public function test_a_registrant_only_touches_their_own_documents(): void
    {
        Storage::fake('public');
        $pendaftar = PpdbRegistrant::factory()->create();
        $punyaOrangLain = PpdbDocument::factory()->create();

        $this->actingAs($pendaftar, 'ppdb')
            ->getJson(route('api.v1.ppdb.berkas.index'))
            ->assertJsonPath('summary.terunggah', 0);

        $this->actingAs($pendaftar, 'ppdb')
            ->deleteJson(route('api.v1.ppdb.berkas.destroy', $punyaOrangLain))
            ->assertNotFound();
    }

    public function test_an_accepted_document_cannot_be_deleted_by_the_registrant(): void
    {
        $pendaftar = PpdbRegistrant::factory()->create();
        $diterima = PpdbDocument::factory()->for($pendaftar, 'registrant')->create(['status' => 'diterima']);

        $this->actingAs($pendaftar, 'ppdb')
            ->deleteJson(route('api.v1.ppdb.berkas.destroy', $diterima))
            ->assertStatus(422);

        $this->assertModelExists($diterima);
    }

    public function test_the_summary_reports_what_is_still_missing(): void
    {
        Storage::fake('public');
        $pendaftar = PpdbRegistrant::factory()->create(['jalur' => 'Reguler 1']);

        // Jalur Reguler wajib lima berkas.
        foreach (['ijazah', 'rapor', 'kartu_keluarga', 'akta'] as $jenis) {
            $this->actingAs($pendaftar, 'ppdb')
                ->postJson(route('api.v1.ppdb.berkas.store'), [
                    'jenis' => $jenis,
                    'file' => UploadedFile::fake()->create("{$jenis}.pdf", 50, 'application/pdf'),
                ])->assertCreated();
        }

        $this->actingAs($pendaftar, 'ppdb')
            ->getJson(route('api.v1.ppdb.berkas.index'))
            ->assertJsonPath('summary.wajib', 5)
            ->assertJsonPath('summary.terunggah', 4)
            ->assertJsonPath('summary.kurang', 1)
            ->assertJsonPath('summary.lengkap', false);
    }

    public function test_a_ppdb_token_cannot_reach_the_other_portals(): void
    {
        $pendaftar = PpdbRegistrant::factory()->create()->createToken('uji')->plainTextToken;
        $siswa = Student::factory()->create()->createToken('uji')->plainTextToken;

        $this->withToken($pendaftar)->getJson(route('api.v1.overview'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($pendaftar)->getJson(route('api.v1.guru.overview'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($pendaftar)->getJson(route('api.v1.library'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($siswa)->getJson(route('api.v1.ppdb.berkas.index'))->assertUnauthorized();
    }

    public function test_the_committee_accepts_and_rejects_documents_from_the_panel(): void
    {
        $this->actingAs(User::factory()->create());
        $pendaftar = PpdbRegistrant::factory()->create();
        $berkas = PpdbDocument::factory()->for($pendaftar, 'registrant')->create();

        Livewire::test(ListPpdbRegistrants::class)->assertSuccessful();

        $manager = Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $pendaftar,
            'pageClass' => EditPpdbRegistrant::class,
        ]);

        $manager->callTableAction('tolak', $berkas, ['note' => 'Hasil pindaian tidak terbaca.']);
        $berkas->refresh();
        $this->assertSame('ditolak', $berkas->status);
        $this->assertSame('Hasil pindaian tidak terbaca.', $berkas->note);
        $this->assertNotNull($berkas->verified_by);

        $manager->callTableAction('terima', $berkas);
        $this->assertSame('diterima', $berkas->refresh()->status);
        $this->assertNull($berkas->note);
    }

    /** Seeder dijalankan ulang tiap `docker compose up`; tidak boleh menggandakan. */
    public function test_the_ppdb_seeder_can_run_twice_without_duplicating(): void
    {
        $this->seed(PpdbSeeder::class);
        $sebelum = PpdbRegistrant::query()->count();

        $this->seed(PpdbSeeder::class);

        $this->assertSame($sebelum, PpdbRegistrant::query()->count());
        $this->assertGreaterThan(0, $sebelum);
    }
}
