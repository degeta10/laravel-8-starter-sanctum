<?php

namespace App\Jobs;

use App\Mail\WelcomeUser;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $email, $userName;

    public function __construct($data)
    {
        $this->email = $data->email;
        $this->userName = $data->userName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new WelcomeUser($this->userName));
    }

    public function failed(Exception $exception)
    {
        Log::error($exception);
    }
}
