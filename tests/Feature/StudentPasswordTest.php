<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StudentPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function masuk(Student $siswa, string $sandi): string
    {
        return $this->postJson(route('api.v1.login'), [
            'identifier' => $siswa->nisn,
            'password' => $sandi,
        ])->json('token');
    }

    private function ganti(string $token, array $data)
    {
        // Token dikirim manual: actingAs tidak punya currentAccessToken().
        return $this->withToken($token)->postJson(route('api.v1.me.password'), $data);
    }

    public function test_a_student_can_change_their_password(): void
    {
        $siswa = Student::factory()->create(['nisn' => '1234567890', 'password' => 'lama12345']);
        $token = $this->masuk($siswa, 'lama12345');

        $this->ganti($token, [
            'current_password' => 'lama12345',
            'password' => 'baruSekali7',
            'password_confirmation' => 'baruSekali7',
        ])->assertOk();

        $this->assertTrue(Hash::check('baruSekali7', $siswa->refresh()->password));
        $this->postJson(route('api.v1.login'), ['identifier' => '1234567890', 'password' => 'lama12345'])
            ->assertUnprocessable();
    }

    public function test_the_current_password_must_be_right(): void
    {
        $siswa = Student::factory()->create(['password' => 'lama12345']);

        $this->ganti($this->masuk($siswa, 'lama12345'), [
            'current_password' => 'salah999',
            'password' => 'baruSekali7',
            'password_confirmation' => 'baruSekali7',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('lama12345', $siswa->refresh()->password));
    }

    /** @return array<string, array{string, string}> */
    public static function sandiLemah(): array
    {
        return [
            'terlalu pendek' => ['ab12', 'ab12'],
            'tanpa angka' => ['hanyahuruf', 'hanyahuruf'],
            'tanpa huruf' => ['1234567890', '1234567890'],
            'konfirmasi beda' => ['baruSekali7', 'baruSekali8'],
            'sama dengan lama' => ['lama12345', 'lama12345'],
        ];
    }

    #[DataProvider('sandiLemah')]
    public function test_weak_or_mismatched_passwords_are_rejected(string $sandi, string $konfirmasi): void
    {
        $siswa = Student::factory()->create(['password' => 'lama12345']);

        $this->ganti($this->masuk($siswa, 'lama12345'), [
            'current_password' => 'lama12345',
            'password' => $sandi,
            'password_confirmation' => $konfirmasi,
        ])->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_the_nisn_cannot_be_used_as_password(): void
    {
        $siswa = Student::factory()->create(['nisn' => 'ab12345678', 'password' => 'lama12345']);

        $this->ganti($this->masuk($siswa, 'lama12345'), [
            'current_password' => 'lama12345',
            'password' => 'ab12345678',
            'password_confirmation' => 'ab12345678',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    /** Sandi diganti = perangkat lain yang masuk dengan sandi lama ikut keluar. */
    public function test_other_sessions_are_signed_out_but_this_one_stays(): void
    {
        $siswa = Student::factory()->create(['password' => 'lama12345']);
        $perangkatLain = $this->masuk($siswa, 'lama12345');
        $perangkatIni = $this->masuk($siswa, 'lama12345');

        $this->ganti($perangkatIni, [
            'current_password' => 'lama12345',
            'password' => 'baruSekali7',
            'password_confirmation' => 'baruSekali7',
        ])->assertOk()->assertJsonPath('other_sessions_revoked', 1);

        // Guard di-cache per permintaan dalam tes; direset supaya token dibaca ulang.
        $this->app['auth']->forgetGuards();
        $this->withToken($perangkatLain)->getJson(route('api.v1.me'))->assertUnauthorized();

        $this->app['auth']->forgetGuards();
        $this->withToken($perangkatIni)->getJson(route('api.v1.me'))->assertOk();
    }

    /** Label "kata sandi baru" khusus endpoint ini, tidak boleh bocor ke login. */
    public function test_the_new_password_label_does_not_leak_into_login(): void
    {
        $pesan = $this->postJson(route('api.v1.login'), ['identifier' => '123'])
            ->assertUnprocessable()
            ->json('errors.password.0');

        $this->assertStringNotContainsString('baru', (string) $pesan);
    }
}
