<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuruLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_teacher_logs_in_with_email_and_gets_a_token(): void
    {
        $guru = Teacher::factory()->create(['email' => 'rini@madrasah.test', 'password' => 'rahasia123']);

        $res = $this->postJson(route('api.v1.login'), [
            'identifier' => 'rini@madrasah.test',
            'password' => 'rahasia123',
        ])->assertOk()
            ->assertJsonPath('role', 'teacher')
            ->assertJsonPath('teacher.id', $guru->id);

        $this->withToken($res->json('token'))
            ->getJson(route('api.v1.guru.me'))
            ->assertOk()
            ->assertJsonPath('email', 'rini@madrasah.test');
    }

    /** NIP sering diketik berkelompok seperti tertulis di SK. */
    public function test_a_teacher_logs_in_with_nip_even_when_typed_with_spaces(): void
    {
        Teacher::factory()->create(['nip' => '198503152010012007', 'password' => 'rahasia123']);

        $this->postJson(route('api.v1.login'), [
            'identifier' => '19850315 201001 2 007',
            'password' => 'rahasia123',
        ])->assertOk()->assertJsonPath('role', 'teacher');
    }

    public function test_a_teacher_without_a_portal_password_cannot_log_in(): void
    {
        Teacher::factory()->tanpaAksesPortal()->create(['email' => 'baru@madrasah.test']);

        $this->postJson(route('api.v1.login'), [
            'identifier' => 'baru@madrasah.test',
            'password' => '',
        ])->assertStatus(422);

        $this->postJson(route('api.v1.login'), [
            'identifier' => 'baru@madrasah.test',
            'password' => 'apa-saja',
        ])->assertStatus(422)
            ->assertJsonPath('errors.identifier.0', 'NISN/NIP/Email atau kata sandi salah.');
    }

    public function test_an_inactive_teacher_is_refused_after_the_password_matches(): void
    {
        Teacher::factory()->inactive()->create(['email' => 'cuti@madrasah.test', 'password' => 'rahasia123']);

        $this->postJson(route('api.v1.login'), [
            'identifier' => 'cuti@madrasah.test',
            'password' => 'rahasia123',
        ])->assertStatus(422)
            ->assertJsonPath('errors.identifier.0', 'Akun ini tidak aktif. Hubungi admin madrasah.');
    }

    public function test_an_email_with_both_accounts_and_the_same_password_goes_to_the_admin_panel(): void
    {
        User::factory()->create(['email' => 'wakasek@madrasah.test', 'password' => 'rahasia123', 'role' => 'kurikulum']);
        Teacher::factory()->create(['email' => 'wakasek@madrasah.test', 'password' => 'rahasia123']);

        $this->postJson(route('api.v1.login'), [
            'identifier' => 'wakasek@madrasah.test',
            'password' => 'rahasia123',
        ])->assertOk()->assertJsonPath('role', 'admin');
    }

    /** Sandi yang berbeda memilih akunnya sendiri. */
    public function test_the_teacher_password_reaches_the_teacher_account_when_the_admin_one_differs(): void
    {
        User::factory()->create(['email' => 'wakasek@madrasah.test', 'password' => 'sandi-admin1', 'role' => 'kurikulum']);
        Teacher::factory()->create(['email' => 'wakasek@madrasah.test', 'password' => 'sandi-guru1']);

        $this->postJson(route('api.v1.login'), [
            'identifier' => 'wakasek@madrasah.test',
            'password' => 'sandi-guru1',
        ])->assertOk()->assertJsonPath('role', 'teacher');
    }

    public function test_teacher_and_student_tokens_only_open_their_own_portal(): void
    {
        $guru = Teacher::factory()->create()->createToken('uji')->plainTextToken;
        $siswa = Student::factory()->create()->createToken('uji')->plainTextToken;

        $this->withToken($guru)->getJson(route('api.v1.overview'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($guru)->getJson(route('api.v1.forum'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($siswa)->getJson(route('api.v1.guru.overview'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();

        // Endpoint bersama menerima keduanya.
        $this->withToken($guru)->getJson(route('api.v1.library'))->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withToken($siswa)->getJson(route('api.v1.library'))->assertOk();
    }

    public function test_logout_revokes_a_teacher_token(): void
    {
        $guru = Teacher::factory()->create();
        $token = $guru->createToken('uji')->plainTextToken;

        $this->withToken($token)->postJson(route('api.v1.logout'))->assertOk();

        $this->assertSame(0, $guru->tokens()->count());
    }

    public function test_deactivating_a_teacher_signs_them_out_everywhere(): void
    {
        $guru = Teacher::factory()->create();
        $guru->createToken('laptop');
        $guru->createToken('ponsel');

        $guru->update(['is_active' => false]);

        $this->assertSame(0, $guru->tokens()->count());
    }

    public function test_deactivating_a_student_signs_them_out_everywhere(): void
    {
        $siswa = Student::factory()->create();
        $siswa->createToken('ponsel');

        $siswa->update(['is_active' => false]);

        $this->assertSame(0, $siswa->tokens()->count());
    }

    /** NIP selalu angka, jadi aturan "harus ada huruf" sudah menolaknya. */
    public function test_a_teacher_can_change_their_password_but_not_to_their_nip(): void
    {
        $guru = Teacher::factory()->create(['nip' => '198001012005011001', 'password' => 'lama12345']);

        $this->actingAs($guru, 'teacher')
            ->postJson(route('api.v1.me.password'), [
                'current_password' => 'lama12345',
                'password' => '198001012005011001',
                'password_confirmation' => '198001012005011001',
            ])->assertStatus(422)
            ->assertJsonValidationErrors('password');
        $this->assertTrue(Hash::check('lama12345', $guru->fresh()->password));

        $guru->createToken('uji');
        $token = $guru->createToken('sekarang')->plainTextToken;
        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->postJson(route('api.v1.me.password'), [
                'current_password' => 'lama12345',
                'password' => 'baru12345',
                'password_confirmation' => 'baru12345',
            ])->assertOk()
            ->assertJsonPath('other_sessions_revoked', 1);
    }

    /**
     * `throttle:5,1` biasa mengunci pada ID saja; siswa #1 dan guru #1 lalu
     * berbagi jatah. Limiter bernama memisahkan keduanya.
     */
    public function test_a_student_and_a_teacher_with_the_same_id_do_not_share_a_rate_limit(): void
    {
        $siswa = Student::factory()->create();
        $guru = Teacher::factory()->create();
        $this->assertSame($siswa->id, $guru->id);

        $salah = ['current_password' => 'salah', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'];

        foreach (range(1, 5) as $_) {
            $this->actingAs($siswa, 'student')->postJson(route('api.v1.me.password'), $salah)->assertStatus(422);
        }
        $this->actingAs($siswa, 'student')->postJson(route('api.v1.me.password'), $salah)->assertStatus(429);

        $this->app['auth']->forgetGuards();
        $this->actingAs($guru, 'teacher')->postJson(route('api.v1.me.password'), $salah)->assertStatus(422);
    }
}
