<?php

namespace Tests\Feature;

use App\Notifications\WelcomeUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_login_using_api()
    {
        $user = \App\Models\User::factory()->create();
        $this->postJson(
            '/api/auth/login',
            [
                'email'     => $user->email,
                'password'  => 'password'
            ]
        )->assertOk()
            ->assertJsonStructure([
                "success",
                "message",
                "data" => [
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
                'name'                  => $this->faker->name(),
                'email'                 => $this->faker->safeEmail(),
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]
        )->assertCreated();
    }

    public function test_registered_event_is_dispatched_when_signup_using_api()
    {
        Event::fake([
            Registered::class,
        ]);
        $this->postJson(
            '/api/auth/signup',
            [
                'name'                  => $this->faker->name(),
                'email'                 => $this->faker->safeEmail(),
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]
        );
        Event::assertDispatched(Registered::class);
    }

    public function test_user_will_receive_verification_email_after_signup_using_api()
    {
        Notification::fake();
        $mail = $this->faker->safeEmail();
        $this->postJson(
            '/api/auth/signup',
            [
                'name'                  => $this->faker->name(),
                'email'                 => $mail,
                'password'              => 'password',
                'password_confirmation' => 'password',
            ]
        );
        $user = \App\Models\User::whereEmail($mail)->first();
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
        )->assertOk();
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
        $this->postJson(
            '/api/auth/login',
            [
                'email'     =>  $this->faker->safeEmail(),
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
        $this->postJson('/api/auth/logout')->assertOk();
    }

    public function test_guest_user_cannot_logout_using_api()
    {
        $this->withoutExceptionHandling();
        $this->expectException('Illuminate\Auth\AuthenticationException');
        $this->postJson('/api/auth/logout');
    }
}
