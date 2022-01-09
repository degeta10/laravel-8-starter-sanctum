<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Jobs\UserWelcomeEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeUserEmail
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
     * @param  \App\Events\UserRegistered  $event
     * @return void
     */
    public function handle(Verified $event)
    {
        $dispatch_at = now()->addSeconds(5);
        if (config('queue.default') == 'redis') {
            UserWelcomeEmail::dispatch($event)->onQueue('default')->delay($dispatch_at);
        } else if (config('queue.default') == 'sync') {
            UserWelcomeEmail::dispatch($event)->delay($dispatch_at);
        }
    }
}
