<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;


class Mail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $system_response;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($system_response)
    {
        $this->system_response = $system_response;
    }

    public function middleware()
    {
        return [new WithoutOverlapping($this->system_response)];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $message = $this->system_response;
        mail("danny@fusebox.co.za","Api Failure",$message);
    }
}
