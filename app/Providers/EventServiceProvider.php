<?php

namespace App\Providers;

use App\Events\UserRegistered;
use App\Listeners\PruneOldTokens;
use App\Listeners\RevokeOldTokens;
use App\Listeners\SendWelcomeUserEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Events\RefreshTokenCreated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserRegistered::class => [
            SendWelcomeUserEmail::class,
        ],
        // AccessTokenCreated::class => [
        //     RevokeOldTokens::class
        // ],
        // RefreshTokenCreated::class => [
        //     PruneOldTokens::class,
        // ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
