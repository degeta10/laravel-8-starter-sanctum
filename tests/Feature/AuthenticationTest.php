<?php

namespace Tests\Feature;

use App\Notifications\WelcomeUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_using_api()
    {
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

    public function test_only_verified_user_can_login_using_api()
    {
        $user = \App\Models\User::factory()->unverified()->create();
        $this->postJson(
            '/api/auth/login',
            [
                'email'     => $user->email,
                'password'  => 'password'
            ]
        )->assertUnauthorized();
    }

    public function test_user_can_signup_using_api()
    {
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

    public function test_registered_event_is_dispatched_when_signup_using_api()
    {
        Event::fake([
            Registered::class,
        ]);
        $this->postJson(
            '/api/auth/signup',
            [
                'name'                  => 'Test customer',
                'email'                 => 'test@test.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]
        );
        Event::assertDispatched(Registered::class);
    }

    public function test_user_will_receive_verification_email_after_signup_using_api()
    {
        Notification::fake();
        $this->postJson(
            '/api/auth/signup',
            [
                'name'                  => 'Test customer',
                'email'                 => 'test@test.com',
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]
        );
        $user = \App\Models\User::whereEmail('test@test.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verification_email_can_be_resent_using_api()
    {
        $user = \App\Models\User::factory()->create();
        $this->postJson(
            '/api/auth/email-verification/resend',
            [
                'email' => $user->email,
            ]
        )->assertStatus(200);
    }

    public function test_unverified_user_will_receive_verification_email_when_resent_using_api()
    {
        Notification::fake();
        $user = \App\Models\User::factory()->unverified()->create();
        $this->postJson(
            '/api/auth/email-verification/resend',
            [
                'email' => $user->email,
            ]
        );
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verified_user_will_not_receive_verification_email_when_resent_using_api()
    {
        Notification::fake();
        $user = \App\Models\User::factory()->create();
        $this->postJson(
            '/api/auth/email-verification/resend',
            [
                'email' => $user->email,
            ]
        );
        Notification::assertNothingSent();
    }

    public function test_user_can_verify_email_using_link()
    {
        $notification = new VerifyEmail();
        $user = \App\Models\User::factory()->unverified()->create();
        $mail = $notification->toMail($user);
        $uri = $mail->actionUrl;
        $this->get($uri);
        $this->assertTrue(\App\Models\User::find($user->id)->hasVerifiedEmail());
    }

    public function test_verified_event_is_dispatched_after_verification()
    {
        Event::fake([Verified::class]);
        $notification = new VerifyEmail();
        $user = \App\Models\User::factory()->unverified()->create();
        $mail = $notification->toMail($user);
        $uri = $mail->actionUrl;
        $this->get($uri);
        $this->assertTrue(\App\Models\User::find($user->id)->hasVerifiedEmail());
        Event::assertDispatched(Verified::class);
    }

    public function test_user_will_receive_welcome_email_after_verification()
    {
        Notification::fake();
        $notification = new VerifyEmail();
        $user = \App\Models\User::factory()->unverified()->create();
        $mail = $notification->toMail($user);
        $uri = $mail->actionUrl;
        $this->get($uri);
        $this->assertTrue(\App\Models\User::find($user->id)->hasVerifiedEmail());
        Notification::assertSentTo($user, WelcomeUser::class);
    }

    public function test_user_cannot_login_with_wrong_password_using_api()
    {
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
        Sanctum::actingAs(
            \App\Models\User::factory()->create(),
            ['*']
        );
        $this->postJson('/api/auth/logout')->assertStatus(200);
    }

    public function test_guest_user_cannot_logout_using_api()
    {
        $this->expectException('Illuminate\Auth\AuthenticationException');
        $this->postJson('/api/auth/logout');
    }
}
