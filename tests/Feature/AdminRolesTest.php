<?php

namespace Tests\Feature;

use App\Filament\Pages\Modules\ELibrary;
use App\Filament\Pages\Modules\SyncData;
use App\Filament\Resources\AlumniAccounts\AlumniAccountResource;
use App\Filament\Resources\Bills\BillResource;
use App\Filament\Resources\CounselingSessions\CounselingSessionResource;
use App\Filament\Resources\CounselingSessions\Pages\ListCounselingSessions;
use App\Filament\Resources\Exams\ExamResource;
use App\Filament\Resources\Letters\LetterResource;
use App\Filament\Resources\NewsPosts\NewsPostResource;
use App\Filament\Resources\Scholarships\ScholarshipResource;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\CounselingSession;
use App\Models\User;
use App\Support\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminRolesTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string, string, int}> */
    public static function matriks(): array
    {
        $u = fn (string $kelas) => $kelas;

        return [
            'kurikulum → ujian' => ['kurikulum', ExamResource::class, 200],
            'kurikulum → siswa' => ['kurikulum', StudentResource::class, 200],
            'kurikulum → tagihan' => ['kurikulum', BillResource::class, 403],
            'kurikulum → konseling' => ['kurikulum', CounselingSessionResource::class, 403],
            'tata usaha → tagihan' => ['tata_usaha', BillResource::class, 200],
            'tata usaha → persuratan' => ['tata_usaha', LetterResource::class, 200],
            'tata usaha → ujian' => ['tata_usaha', ExamResource::class, 403],
            'humas → berita' => ['humas', NewsPostResource::class, 200],
            'humas → siswa' => ['humas', StudentResource::class, 403],
            'bk → konseling' => ['bk', CounselingSessionResource::class, 200],
            'bk → persuratan' => ['bk', LetterResource::class, 403],
            'pustakawan → tagihan' => ['pustakawan', BillResource::class, 403],
            'humas → akun alumni' => ['humas', AlumniAccountResource::class, 200],
            'humas → beasiswa' => ['humas', ScholarshipResource::class, 200],
            'kurikulum → beasiswa' => ['kurikulum', ScholarshipResource::class, 403],
            'pustakawan → akun alumni' => ['pustakawan', AlumniAccountResource::class, 403],
            'super admin → pengguna' => ['super_admin', UserResource::class, 200],
            'kurikulum → pengguna' => ['kurikulum', UserResource::class, 403],
        ];
    }

    /**
     * URL yang diketik langsung harus ditolak, bukan hanya menunya yang
     * disembunyikan.
     *
     * @param  class-string  $resource
     */
    #[DataProvider('matriks')]
    public function test_each_role_reaches_only_its_modules(string $peran, string $resource, int $status): void
    {
        $this->actingAs(User::factory()->peran($peran)->create())
            ->get($resource::getUrl('index'))
            ->assertStatus($status);
    }

    public function test_custom_module_pages_are_restricted_too(): void
    {
        $pustakawan = User::factory()->peran('pustakawan')->create();

        $this->actingAs($pustakawan)->get(ELibrary::getUrl())->assertOk();
        $this->actingAs($pustakawan)->get(SyncData::getUrl())->assertForbidden();
    }

    public function test_every_role_can_open_the_dashboard(): void
    {
        foreach (array_keys(Peran::LABEL) as $peran) {
            $this->actingAs(User::factory()->peran($peran)->create())
                ->get('/admin')
                ->assertOk();
        }
    }

    public function test_an_account_without_a_role_cannot_enter_the_panel(): void
    {
        $this->actingAs(User::factory()->peran('')->create(['role' => null]))
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_the_login_gate_refuses_an_account_without_a_role(): void
    {
        User::factory()->create(['email' => 'tanpaperan@madrasah.test', 'password' => 'rahasia123', 'role' => null]);

        $this->postJson(route('api.v1.login'), ['identifier' => 'tanpaperan@madrasah.test', 'password' => 'rahasia123'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.identifier.0', 'Akun ini belum diberi peran. Hubungi Admin Utama.');
    }

    /** Catatan rahasia hanya untuk Guru BK — Admin Utama pun tidak. */
    public function test_confidential_counseling_notes_are_for_the_counselor_only(): void
    {
        $rahasia = CounselingSession::factory()->create(['is_confidential' => true, 'summary' => 'Isi rahasia']);
        CounselingSession::factory()->create(['is_confidential' => false, 'summary' => 'Isi biasa']);

        $this->actingAs(User::factory()->peran('bk')->create());
        Livewire::test(ListCounselingSessions::class)->assertCanSeeTableRecords([$rahasia]);

        $this->actingAs(User::factory()->peran('super_admin')->create());
        Livewire::test(ListCounselingSessions::class)->assertCanNotSeeTableRecords([$rahasia]);

        // Lewat URL sunting langsung pun tidak bisa.
        $this->get(CounselingSessionResource::getUrl('edit', ['record' => $rahasia]))->assertNotFound();
    }

    public function test_the_last_super_admin_cannot_be_demoted_or_deleted(): void
    {
        $satuSatunya = User::factory()->peran('super_admin')->create();

        try {
            $satuSatunya->update(['role' => 'humas']);
            $this->fail('Admin Utama terakhir berhasil diturunkan.');
        } catch (\RuntimeException) {
        }

        try {
            $satuSatunya->delete();
            $this->fail('Admin Utama terakhir berhasil dihapus.');
        } catch (\RuntimeException) {
        }

        // Diperiksa langsung di basis data: nilai di memori sudah kotor karena
        // update yang ditolak — persis keadaan yang dulu meloloskan penghapusan.
        $this->assertSame(Peran::SUPER_ADMIN, User::query()->find($satuSatunya->id)?->role);
    }

    public function test_a_super_admin_can_be_demoted_when_another_remains(): void
    {
        $a = User::factory()->peran('super_admin')->create();
        User::factory()->peran('super_admin')->create();

        $a->update(['role' => 'humas']);

        $this->assertSame('humas', $a->refresh()->role);
    }

    /**
     * Di panel, satu-satunya jalan menurunkan Admin Utama terakhir adalah
     * menyunting peran sendiri — dan kolom itu dikunci. Nilai yang dipaksa
     * masuk ke kolom terkunci tidak ikut tersimpan.
     */
    public function test_a_super_admin_cannot_change_their_own_role_in_the_panel(): void
    {
        $saya = User::factory()->peran('super_admin')->create();
        $this->actingAs($saya);

        Livewire::test(EditUser::class, ['record' => $saya->getKey()])
            ->assertFormFieldIsDisabled('role')
            ->fillForm(['role' => 'humas'])
            ->call('save');

        $this->assertSame(Peran::SUPER_ADMIN, $saya->refresh()->role);
    }

    public function test_a_super_admin_can_change_someone_elses_role(): void
    {
        $this->actingAs(User::factory()->peran('super_admin')->create());
        $lain = User::factory()->peran('humas')->create();

        Livewire::test(EditUser::class, ['record' => $lain->getKey()])
            ->fillForm(['role' => 'pustakawan'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('pustakawan', $lain->refresh()->role);
    }
}
