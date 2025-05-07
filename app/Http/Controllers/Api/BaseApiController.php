<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

use App\Models\UserActivity;
class BaseApiController extends Controller
{
    protected $member_role_id;
    function __construct() {
        $this->member_role_id = env('MEMBER_ROLE_ID');
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

        UserActivity::create([
                'user_email'        => Auth::check() ? auth()->user()->email : '',
                'user_name'         => Auth::check() ? auth()->user()->name : '',
                'user_type'         => 'USER',
                'ip_address'        => $_SERVER['REMOTE_ADDR']??'0',
                'activity_type'     => 1,
                'activity_details'  => $message,
                'platform_type'     => 'MOBILE',
            ]);

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
        UserActivity::create([
                'user_email'        => Auth::check() ? auth()->user()->email : 'unauthenticated',
                'user_name'         => Auth::check() ? auth()->user()->name : 'unauthenticated',
                'user_type'         => 'USER',
                'ip_address'        => $_SERVER['REMOTE_ADDR'],
                'activity_type'     => 1,
                'activity_details'  => json_encode($errorMessages),
                'platform_type'     => 'MOBILE',
            ]);

        return response()->json($response, $code);
    }

}
