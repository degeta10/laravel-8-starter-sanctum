<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_user_can_login_using_api()
    {
        $this->withoutExceptionHandling();
        $this->artisan('migrate:fresh');
        $this->artisan('passport:install');
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
                "refresh_token",
                "expires_at",
            ]
        ]);
        $this->artisan('migrate:reset');
    }

    public function test_user_can_signup_using_api()
    {
        $this->withoutExceptionHandling();
        $this->artisan('migrate:fresh');
        $this->artisan('passport:install');
        $this->postJson(
            '/api/auth/signup',
            [
                'name'                  => 'Test customer',
                'email'                 => 'test@test.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]
        )->assertStatus(200);
        $this->artisan('migrate:reset');
    }

    public function test_user_cannot_login_with_wrong_password_using_api()
    {
        $this->withoutExceptionHandling();
        $this->artisan('migrate:fresh');
        $this->artisan('passport:install');
        $user = \App\Models\User::factory()->create();
        $this->postJson(
            '/api/auth/login',
            [
                'email'     => $user->email,
                'password'  => 'password1'
            ]
        )->assertUnauthorized();
        $this->artisan('migrate:reset');
    }

    public function test_user_cannot_login_with_wrong_email_using_api()
    {
        $this->withoutExceptionHandling();
        $this->artisan('migrate:fresh');
        $this->artisan('passport:install');
        $user = \App\Models\User::factory()->create();
        $this->postJson(
            '/api/auth/login',
            [
                'email'     => $user->email . "test",
                'password'  => 'password1'
            ],
        )->assertUnprocessable();
        $this->artisan('migrate:reset');
    }

    public function test_loggedin_user_can_logout_using_api()
    {
        $this->withoutExceptionHandling();
        $this->artisan('migrate:fresh');
        $this->artisan('passport:install');

        Passport::actingAs(
            \App\Models\User::factory()->create(),
            ['*']
        );

        $this->postJson('/api/auth/logout')->assertStatus(200);
        $this->artisan('migrate:reset');
    }

    public function test_guest_user_cannot_logout_using_api()
    {
        $this->withoutExceptionHandling();
        $this->expectException('Illuminate\Auth\AuthenticationException');
        $this->postJson('/api/auth/logout');
    }

    public function test_loggedin_user_can_refresh_token_using_api()
    {
        $this->withoutExceptionHandling();
        $this->artisan('migrate:fresh');
        $this->artisan('passport:install');
        $user = \App\Models\User::factory()->create();
        $response = $this->postJson(
            '/api/auth/login',
            [
                'email'     => $user->email,
                'password'  => 'password'
            ]
        );
        $refreshToken = $response['data']['refresh_token'];
        $this->postJson(
            '/api/auth/token/refresh',
            ['token' => $refreshToken]
        )
            ->assertStatus(200)
            ->assertJsonStructure([
                "data" => [
                    "message",
                    "token_type",
                    "access_token",
                    "refresh_token",
                    "expires_at",
                ]
            ]);
        $this->artisan('migrate:reset');
    }
}
