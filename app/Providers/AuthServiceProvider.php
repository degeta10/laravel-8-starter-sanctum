<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        # FOR PRODUCTION
        if (config('app.env') == 'production') {
            Passport::tokensExpireIn(now()->addMinutes(30));
            Passport::refreshTokensExpireIn(now()->addDay());
            Passport::personalAccessTokensExpireIn(now()->addMinutes(30));
        } else {
            # FOR TESTING
            // Passport::tokensExpireIn(now()->addSeconds(2));
            // Passport::refreshTokensExpireIn(now()->addSeconds(10));
            // Passport::personalAccessTokensExpireIn(now()->addSeconds(2));

            # FOR LOCAL DEVELOPMENT
            Passport::tokensExpireIn(now()->addYear());
            Passport::refreshTokensExpireIn(now()->addYear());
            Passport::personalAccessTokensExpireIn(now()->addYear());
        }
    }
}
