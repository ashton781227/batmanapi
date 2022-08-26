<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Models\Panic;
use App\Models\ApiLog;
use App\Http\Resources\PanicResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Jobs\CreatePanic;
use App\Jobs\Mail;
use App\Jobs\CancelPanic;


class PanicController extends Controller
{

    public function index()
    {
        $panics_array = array();
        $panics = Panic::all();
        foreach ($panics as $panic) {
            $array = array(
                 'id' => $panic->id,
                 'longitude' => $panic->longitude,
                 'latitude' => $panic->latitude,
                 'panic_type' => $panic->panic_type,
                 'details' =>$panic->details,
                 'created_at' =>$panic->created_at,
                 'created_by' => json_decode($panic->created_by),
                );

                array_push($panics_array, $array );
            }
        return response([ 'status'=>'success', 'message' => 'Action Completed successfully','data' =>['Panics' => $panics_array]], 200);
    }


    public function create(Request $request)
    {
        
        $data = $request->all();

        // validation
        $validator = Validator::make($data, [
            'longitude' => 'required|string',
            'latitude' => 'required|string',
            'panic_type' => 'nullable|string',
            'details' => 'nullable|string'
        ]);

        $user = Auth::user();
        $user_id = Auth::id();

        // validation
        if($validator->fails()){

            $system_response =response(['error' => $validator->errors(), 
            'Validation Error'], 400);
            Mail::dispatch($system_response)->delay(now()->addMinutes(0.1));
            return $system_response;
        }

        else{
            
            // creating panic record
            $panic = new Panic;
            $panic->longitude = $data['longitude'];
            $panic->latitude = $data['latitude'];
            $panic->panic_type = $data['panic_type'];
            $panic->details = $data['details'];
            $panic->created_by = json_encode(['id'=>$user->id,'name'=>$user->name,'email'=>$user->email]);
            // $panic->variables = json_encode($data);
            // $panic->response = null;
            $panic->save();
            $panic_id = $panic->id;
            
            $system_response = response([ 'status'=>'success', 'message' => 'Panic raised successfully','data' =>['panic_id' => $panic_id]], 200);
 
        }

        // Wayne API
        $wayne_variables = array(
            'name' => 'Steve',
            'role' => 'Network Administrator',
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude'],
            'panic_type' => $data['panic_type'],
            'details' => $data['details'],
            'reference_id' => $panic_id,
            'user_name' => $user->name,
        );

        // Queue for server to server Api calls
        CreatePanic::dispatch($user_id, $data, $system_response, $wayne_variables, $panic_id  )->delay(now()->addMinutes(1));
        
        
        
        // returning system panic response
        return $system_response;

    }


    public function cancel(Request $request)
    {
        $data = $request->all();
        $user_id = Auth::id();

        $validator = Validator::make($data, [
            'panic_id' => 'required|integer'
        ]);

        if($validator->fails()){

            $system_response = response(['error' => $validator->errors(), 
            'Validation Error'], 400);
            Mail::dispatch($system_response)->delay(now()->addMinutes(0.1));
            $api_log = new ApiLog;
            $api_log->user_id = $user_id;
            $api_log->system_variables = json_encode($data);
            $api_log->system_response = json_encode($system_response);
            $api_log->save();
            return $system_response;
        }

        else{

            $panic = Panic::where('id', $data['panic_id'])->first();

            if($panic === null){

                $system_response = response(['status'=>'error','error_message' => 'Panic not found'], 400);
                Mail::dispatch($system_response)->delay(now()->addMinutes(0.1));
                $api_log = new ApiLog;
                $api_log->user_id = $user_id;
                $api_log->system_variables = json_encode($data);
                $api_log->system_response = json_encode($system_response);
                $api_log->save();
                return $system_response;
            }

            else{

                

                // Wayne API
                $wayne_variables = array(
                    'panic_id' => $panic->external_panic_id
                    
                );
                
                $panic->delete();
                  
                $system_response = response([ 'status'=>'success', 'message' => 'Panic cancelled successfully','data' =>[]], 200);

                // Queue for server to server Api calls
                CancelPanic::dispatch($user_id, $data, $system_response, $wayne_variables  )->delay(now()->addMinutes(1)); 

                return $system_response;

            }
        }

        
    }

}
