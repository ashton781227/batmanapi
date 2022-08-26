<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Panic;
use App\Models\ApiLog;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class CancelPanic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id, $data, $system_response, $wayne_variables;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $data, $system_response, $wayne_variables)
    {
        $this->user_id = $user_id;
        $this->wayne_variables = $wayne_variables;
        $this->data = $data;
        $this->system_response = $system_response;
        
        

    }

    public function middleware()
    {
        return [new WithoutOverlapping($this->user_id)];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $wayne_response = Http::withToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYjFhNjMxZTI0MzIwNjM2ODg3MzM2ODlhMzYxYmZkNGFjMTc2YmIyMWEwY2NjMzdiZTRlN2VhYjA1MTA1YWZlYWM0Y2NlNDQ5ZDU2ODE3MDUiLCJpYXQiOjE2NjE0MjQ1NzUsIm5iZiI6MTY2MTQyNDU3NSwiZXhwIjoxNjkyOTYwNTc1LCJzdWIiOiIxMiIsInNjb3BlcyI6W119.JwSfutW6Ry6DmyI8GKFA7JKip73-bH58hezbaBjGuKRO745C3XviHdCYTfsiVYpT6OSyPYgaJ9Vjj0QKc64wgBjo_5mus6FeQZBr1JLV1KRERbuypAiJnp42SpFcZfORHnItSryRtqyhY5WMBWS7vHXYaalGJgHSnQRIM4biwp9KRA7BBSOGSUUmKwlCO6RuOvQ_AjfgPceiRcPFBaeiK1135x8C6HeeYO_rOeGX4GDcJLNhdTtEcCiMicWBD2QmSK1G_6Y6rUmSIw5MoNxFRCHeHDANJdH_yGmujXEJcMmT8D7URM8IzxJtci8FLeLuRD_WaFp7-s1RPUnbGYQKlWp3ED4jeBcEvndxqfUQK2cSJWaN97XNHX7DRfDZE29bHdTVDceDyF0xmJXlRBPq293rFsKz9UkMaxgy9EicHAwnzr6PkCccjZzvGAVqAHAyt8OakVto0971KxGRjV2e6nqw6K4bs_6AIvQZ2ycPm2CJE-qa7EkhAqPkIcfk3IBCEHRJAfzsNvIPQVOQ4PjFBxGPeaLwDGH7pmVmiJ_ARsJF3QnY96AYleBoTscOnUnTJky1oZG7Uke6rx0pdorJUvaovRhz1MyIA1oz2mYKKwxEtYYS6Mr5DS7lekbs9ft5I_RBt2jppPax4vn_t7A2N16qxANHfMFmkdBhXRpM8_o')
                ->accept('application/json')->post('https://wayne.fusebox-staging.co.za/api/v1/panic/cancel', $this->wayne_variables);

                // Log both responses to the DB
                $api_log = new ApiLog;
                $api_log->user_id = $this->user_id;
                $api_log->system_variables = json_encode($this->data);
                $api_log->system_response = json_encode($this->system_response);
                $api_log->wayne_variables = json_encode($this->wayne_variables);
                $api_log->wayne_response = json_encode($wayne_response);
                $api_log->save();
    }
}
