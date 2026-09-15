<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_receives_a_token_with_valid_credentials(): void
    {
        Student::factory()->create([
            'nisn' => '009283741',
            'password' => 'rahasia123',
        ]);

        $response = $this->postJson(route('api.v1.login'), [
            'identifier' => '009283741',
            'password' => 'rahasia123',
        ]);

        $response->assertOk()
            ->assertJsonPath('role', 'student')
            ->assertJsonStructure(['token', 'student' => ['id', 'nisn', 'name', 'kelas']]);

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_fails_with_the_wrong_password(): void
    {
        Student::factory()->create([
            'nisn' => '009283741',
            'password' => 'rahasia123',
        ]);

        $this->postJson(route('api.v1.login'), [
            'identifier' => '009283741',
            'password' => 'salah',
        ])->assertStatus(422)->assertJsonValidationErrors('identifier');
    }

    public function test_login_fails_for_an_unknown_nisn(): void
    {
        $this->postJson(route('api.v1.login'), [
            'identifier' => '000000000',
            'password' => 'rahasia123',
        ])->assertStatus(422)->assertJsonValidationErrors('identifier');
    }

    public function test_inactive_student_cannot_log_in(): void
    {
        Student::factory()->inactive()->create([
            'nisn' => '009283741',
            'password' => 'rahasia123',
        ]);

        $this->postJson(route('api.v1.login'), [
            'identifier' => '009283741',
            'password' => 'rahasia123',
        ])->assertStatus(422)->assertJsonValidationErrors('identifier');
    }

    public function test_identifier_and_password_are_required(): void
    {
        $this->postJson(route('api.v1.login'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['identifier', 'password']);
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $student = Student::factory()->create(['password' => 'rahasia123']);
        $token = $student->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson(route('api.v1.logout'))
            ->assertOk();

        $this->assertSame(0, $student->tokens()->count());
    }

    public function test_protected_routes_reject_requests_without_a_token(): void
    {
        $this->getJson(route('api.v1.me'))->assertUnauthorized();
        $this->getJson(route('api.v1.overview'))->assertUnauthorized();
        $this->getJson(route('api.v1.report-card'))->assertUnauthorized();
    }
}
