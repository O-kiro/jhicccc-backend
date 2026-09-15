<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminHandoffTest extends TestCase
{
    use RefreshDatabase;

    private function login(User $user, string $password = 'rahasia123'): string
    {
        return $this->postJson(route('api.v1.login'), [
            'identifier' => $user->email,
            'password' => $password,
        ])->json('redirect_url');
    }

    public function test_admin_login_returns_a_handoff_link_instead_of_a_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@mankotabatu.sch.id',
            'password' => 'rahasia123',
        ]);

        $response = $this->postJson(route('api.v1.login'), [
            'identifier' => $user->email,
            'password' => 'rahasia123',
        ]);

        $response->assertOk()
            ->assertJsonPath('role', 'admin')
            ->assertJsonStructure(['role', 'name', 'redirect_url']);

        // Admin tidak boleh mendapat token Sanctum — panel memakai sesi.
        $this->assertNull($response->json('token'));
        $this->assertStringContainsString('/auth/handoff', $response->json('redirect_url'));
    }

    public function test_admin_login_fails_with_the_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'admin@mankotabatu.sch.id',
            'password' => 'rahasia123',
        ]);

        $this->postJson(route('api.v1.login'), [
            'identifier' => 'admin@mankotabatu.sch.id',
            'password' => 'salah',
        ])->assertStatus(422)->assertJsonValidationErrors('identifier');
    }

    public function test_unknown_email_is_rejected(): void
    {
        $this->postJson(route('api.v1.login'), [
            'identifier' => 'bukan-siapa-siapa@contoh.test',
            'password' => 'rahasia123',
        ])->assertStatus(422)->assertJsonValidationErrors('identifier');
    }

    public function test_handoff_link_signs_the_admin_in(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $this->get($this->login($user))->assertRedirect('/admin');

        $this->assertTrue(Auth::guard('web')->check());
        $this->assertSame($user->id, Auth::guard('web')->id());
    }

    public function test_handoff_link_can_only_be_used_once(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);
        $url = $this->login($user);

        $this->get($url)->assertRedirect('/admin');

        // Pemakaian kedua harus ditolak, bahkan dari sesi yang masih segar.
        Auth::guard('web')->logout();
        $this->get($url)->assertRedirect('/admin/login');
        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_expired_handoff_link_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);
        $url = $this->login($user);

        $this->travel(61)->seconds();

        $this->get($url)->assertRedirect('/admin/login');
        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_made_up_token_is_rejected(): void
    {
        $this->get(route('admin.handoff', ['token' => str_repeat('a', 64)]))
            ->assertRedirect('/admin/login');

        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_handoff_without_a_token_is_rejected(): void
    {
        $this->get('/auth/handoff')->assertRedirect('/admin/login');
        $this->assertFalse(Auth::guard('web')->check());
    }
}
