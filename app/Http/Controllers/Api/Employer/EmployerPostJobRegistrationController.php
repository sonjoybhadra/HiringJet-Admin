<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Models\User;
use App\Models\UserEmployer;
use App\Models\City;
use App\Models\Country;
use App\Models\State;

use App\Mail\SignupOtp;
use App\Mail\RegistrationSuccess;


class EmployerPostJobRegistrationController extends BaseApiController
{
    private $employer, $employer_user, $otp_validation_time;
    public function __construct()
    {
        $this->employer = env('EMPLOYER_ROLE_ID');
        $this->employer_user = env('EMPLOYER_USER_ROLE_ID');
        $this->otp_validation_time = env('OTP_VALIDATION_DURATION_MINUTES');
    }

    public function checkEmail(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:100|unique:users',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return $this->sendResponse([], 'Email is available.');
    }
    /**
     * Registered member step 1.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function registration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:100|unique:users',
            'country_code' => 'required|max:5',
            'phone' => 'required|max:15|unique:users',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $otp = mt_rand(1111, 9999);
            $otp_mail_hash = base64_encode($otp);

            $user_id = User::insertGetId([
                'role_id'=> $this->employer,
                'first_name'=> "Change",
                'last_name'=> "Name",
                'email'=> $request->email,
                'country_code' => $request->country_code,
                'phone'=> $request->phone,
                'password'=> Hash::make($request->password),
                'status'=> 1,
                'remember_token' => '',
                'email_verified_at' =>date('Y-m-d H:i:s')
            ]);

            if($user_id){
                UserEmployer::insert([
                    'user_id'=> $user_id,
                    'first_name'=> "Change",
                    'last_name'=> "Name",
                    'email'=> $request->email,
                    'country_code'=> $request->country_code,
                    'phone' => $request->phone,
                    'business_id'=> 0,
                    'designation_id'=>0,
                    'completed_steps'=> 2
                ]);

                $user = User::with('user_employer_details')->findOrFail($user_id);
                $full_name = $user->first_name.' '.$user->last_name;
                $message = 'Registration step 1 has successfully done. Please verify activation OTP.';
                //Mail::to($request->email)->send(new SignupOtp($full_name, $otp, $message, 'Signup OTP'));
                $token = JWTAuth::fromUser($user);
                // Set guard to "api" for the current request
                auth()->setUser($user);
                return $this->sendResponse([
                    'token_type' => 'bearer',
                    'token' => $token,
                    'user' => $this->getEmployerDetails(),
                    'expires_in' => config('jwt.ttl') * 60
                ], 'Your account creation has successfully done. Now you can continue and complete your profile.');
            }else{
                return $this->sendError('Error', 'Sorry!! Unable to signup.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function postJobComplete(Request $request){
        $user = User::with('user_employer_details')->findOrFail(auth()->user()->id);

    }
}
