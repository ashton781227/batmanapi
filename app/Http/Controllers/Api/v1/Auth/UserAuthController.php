<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\ApiLog;

class UserAuthController extends Controller
{
    

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($data)) {

            $system_response = response(['status'=>'error','error_message' => 'Missing/incorrect variables. Please try again'], 400);

            $user_id = null;

            Mail::dispatch($system_response)->delay(now()->addMinutes(0.1));
            return $system_response;
        }

        else {

            $token = auth()->user()->createToken('API Token')->accessToken;

            $user_id = Auth::id();

            $system_response = response(['status'=>'success', 'message' => 'Action completed successfully.','data' =>['api_access_token' => $token]], 200);

        }

        
        $api_log = new ApiLog;
        $api_log->user_id = $user_id;
        $api_log->system_variables = json_encode($data);
        $api_log->system_response = json_encode($system_response);
        $api_log->save();

        return $system_response;

    }
}
