<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Events\RefreshTokenCreated;

class PruneOldTokens
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Laravel\Passport\Events\RefreshTokenCreated  $event
     * @return void
     */
    public function handle(RefreshTokenCreated $event)
    {
        DB::table('oauth_refresh_tokens')
            ->whereDate('expires_at', '<', now()->addDays(1))
            ->delete();
    }
}
