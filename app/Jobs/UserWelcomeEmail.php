<?php

namespace App\Jobs;

use App\Mail\WelcomeUser;
use App\Models\User;
use App\Notifications\WelcomeUser as NotificationsWelcomeUser;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class UserWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $user;

    public function __construct($data)
    {
        $this->user = $data->user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notifiable = User::whereEmail($this->user->email)->first();
        $notifiable->notify(new NotificationsWelcomeUser($notifiable));
    }

    public function failed(Exception $exception)
    {
        Log::error($exception);
    }
}
