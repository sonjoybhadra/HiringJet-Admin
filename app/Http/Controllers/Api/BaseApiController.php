<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

use App\Models\UserActivity;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\ProfileComplete;
use App\Models\UserProfileCompletedPercentage;
class BaseApiController extends Controller
{
    function __construct() {
        //
    }
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
    */
    public function sendResponse($result = [], $message = 'Request response is here.')
    {
    	$response = [
            'success' => true,
            'message' => $message,
            'data'    => $result
        ];

        /* UserActivity::create([
                'user_email'        => Auth::check() ? auth()->user()->email : '',
                'user_name'         => Auth::check() ? auth()->user()->name : '',
                'user_type'         => 'USER',
                'ip_address'        => $_SERVER['REMOTE_ADDR']??'0',
                'activity_type'     => 1,
                'activity_details'  => $message,
                'platform_type'     => 'MOBILE',
            ]); */

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
    */
    public function sendError($error, $errorMessages = [], $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
    	$response = [
            'success' => false,
            'error' => $errorMessages,
            'error_str'=> $error
        ];

        /* if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        } */
        /* UserActivity::create([
                'user_email'        => Auth::check() ? auth()->user()->email : 'unauthenticated',
                'user_name'         => Auth::check() ? auth()->user()->name : 'unauthenticated',
                'user_type'         => 'USER',
                'ip_address'        => $_SERVER['REMOTE_ADDR'],
                'activity_type'     => 1,
                'activity_details'  => json_encode($errorMessages),
                'platform_type'     => 'MOBILE',
            ]); */

        return response()->json($response, $code);
    }

    public function getUserDetails(){
        return User::where('id', auth()->user()->id??$user_id)
                                ->with('user_profile')
                                ->with('user_skills')
                                ->with('user_employments')
                                ->with('user_education')
                                ->with('user_profile_completed_percentages')
                                ->with('user_languages')
                                ->with('user_cv')
                                ->first();
    }

    private function get_profile_completed_status($user_id, $completed_slug){
        $has_data = UserProfileCompletedPercentage::where('user_id', $user_id)->where('slug', $completed_slug)->count();
        if($has_data > 0){
            return true;
        }else{
            return false;
        }
    }

    public function calculate_profile_completed_percentage($user_id, $slug){
        $profile_data = UserProfile::select('profile_completed_percentage')
                                                        ->where('user_id', $user_id)
                                                        ->first();
        $profile_completed_percentage = $profile_data ? $profile_data->profile_completed_percentage : 0;

        $create_data_array = [];
        if(!$this->get_profile_completed_status($user_id, $slug)){
            $percentage = ProfileComplete::where('status', 1)->where('slug', $slug)->first();
            if($percentage){
                $profile_completed_percentage += $percentage->percentage;
                $create_data_array = [
                    'user_id'=> $user_id,
                    'profile_completes_id'=> $percentage->id,
                    'slug'=> $percentage->slug,
                    'percentage'=> $percentage->percentage,
                    'perticulars'=> $percentage->name,
                ];

                UserProfileCompletedPercentage::create($create_data_array);

                UserProfile::where('user_id', $user_id)->update([
                    'profile_completed_percentage'=> $profile_completed_percentage
                ]);
            }
        }
    }

}
