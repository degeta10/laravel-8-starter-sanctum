<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_using_api()
    {
        $this->withoutExceptionHandling();
        $user = \App\Models\User::factory()->create();
        $this->postJson(
            '/api/auth/login',
            [
                'email'     => $user->email,
                'password'  => 'password'
            ]
        )->assertStatus(200)->assertJsonStructure([
            "data" => [
                "message",
                "user" => [
                    'name',
                    'email',
                ],
                "token_type",
                "access_token",
            ]
        ]);
    }

    public function test_user_can_signup_using_api()
    {
        $this->withoutExceptionHandling();
        $this->postJson(
            '/api/auth/signup',
            [
                'name'                  => 'Test customer',
                'email'                 => 'test@test.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]
        )->assertStatus(200);
    }

    public function test_user_cannot_login_with_wrong_password_using_api()
    {
        $this->withoutExceptionHandling();
        $user = \App\Models\User::factory()->create();
        $this->postJson(
            '/api/auth/login',
            [
                'email'     => $user->email,
                'password'  => 'password1'
            ]
        )->assertUnauthorized();
    }

    public function test_user_cannot_login_with_wrong_email_using_api()
    {
        $this->withoutExceptionHandling();
        $user = \App\Models\User::factory()->create();
        $this->postJson(
            '/api/auth/login',
            [
                'email'     => $user->email . "test",
                'password'  => 'password1'
            ],
        )->assertUnprocessable();
    }

    public function test_loggedin_user_can_logout_using_api()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            \App\Models\User::factory()->create(),
            ['*']
        );
        $this->postJson('/api/auth/logout')->assertStatus(200);
    }

    public function test_guest_user_cannot_logout_using_api()
    {
        $this->withoutExceptionHandling();
        $this->expectException('Illuminate\Auth\AuthenticationException');
        $this->postJson('/api/auth/logout');
    }
}
