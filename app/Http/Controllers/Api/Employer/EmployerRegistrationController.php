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
use App\Mail\SignupOtp;
use App\Mail\RegistrationSuccess;

class EmployerRegistrationController extends BaseApiController
{
    private $employer, $employer_user, $otp_validation_time;
    public function __construct()
    {
        $this->employer = env('EMPLOYER_ROLE_ID');
        $this->employer_user = env('EMPLOYER_USER_ROLE_ID');
        $this->otp_validation_time = env('OTP_VALIDATION_DURATION_MINUTES');
    }
    /**
     * Registered member step 1.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function registration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email|max:100|unique:users',
            'country_code' => 'required|max:5',
            'phone' => 'required|max:15|unique:users',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
            'business_id' => 'required|integer',
            'designation_id' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $otp = mt_rand(1111, 9999);
            $otp_mail_hash = base64_encode($otp);

            $user_id = User::insertGetId([
                'role_id'=> $this->employer,
                'first_name'=> $request->first_name,
                'last_name'=> $request->last_name,
                'email'=> $request->email,
                'country_code' => $request->country_code,
                'phone'=> $request->phone,
                'password'=> Hash::make($request->password),
                'status'=> 0,
                'remember_token' => $otp_mail_hash,
                'email_verified_at' => date('Y-m-d H:i:s', strtotime('+'.$this->otp_validation_time.' minutes'))
            ]);

            if($user_id){
                UserEmployer::insert([
                    'user_id'=> $user_id,
                    'first_name'=> $request->first_name,
                    'last_name'=> $request->last_name,
                    'email'=> $request->email,
                    'country_code'=> $request->country_code,
                    'phone' => $request->phone,
                    'business_id'=> $request->is_experienced
                ]);

                $full_name = $request->first_name.' '.$request->last_name;
                $message = 'Registration step 1 has successfully done. Please verify activation OTP.';
                Mail::to($request->email)->send(new SignupOtp($full_name, $otp, $message, 'Signup OTP'));

                return $this->sendResponse([
                    'otp'=> $otp,
                    'email'=> $request->email
                ], 'Registration step 1 has done. Please verify OTP already send in your registered email.');
            }else{
                return $this->sendError('Error', 'Sorry!! Unable to signup.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Resend password with validity 10 minutes.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user = User::where('email', $request->email)->where('status', 0)->first();
        if(!$user){
            return $this->sendError('Error', 'Request email is not found.', Response::HTTP_UNAUTHORIZED);
        }

        $otp = mt_rand(1111, 9999);
        $otp_mail_hash = base64_encode($otp);

        $user->remember_token = $otp_mail_hash;
        $user->email_verified_at = date('Y-m-d H:i:s', strtotime('+'.$this->otp_validation_time.' minutes'));
        $user->save();

        $full_name = $user->first_name.' '.$user->last_name;
        $message = 'Registration step 1 has successfully done. Please verify activation OTP.';
        Mail::to($request->email)->send(new SignupOtp($full_name, $otp, $message, 'Signup OTP'));

        return $this->sendResponse([
            'otp'=> $otp,
            'email'=> $request->email
        ], 'OTP resend successfully. Please verify OTP already send in your registered email.');
    }

    /**
     * Registration OTP verification to complete registration.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function registerVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|min:4|max:4',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('email', $request->email)
                        ->where('status',  0)
                        ->where('remember_token',  base64_encode($request->otp))
                        ->with('user_employer_details')
                        ->first();

        if(!$user){
            return $this->sendError('Error', 'Request email is not found Or OTP not matched.', Response::HTTP_UNAUTHORIZED);
        }

        $current_dt = date('Y-m-d H:i:s');
        if($current_dt > $user->email_verified_at ){
            return $this->sendError('Warning', 'OTP validation time expired.', Response::HTTP_UNAUTHORIZED);
        }

        $token = JWTAuth::fromUser($user);
        // Set guard to "api" for the current request
        auth()->setUser($user);

        $user_obj = User::find($user->id);
        $user_obj->status = 1;
        $user_obj->remember_token = '';
        $user_obj->email_verified_at = date('Y-m-d H:i:s');
        $user_obj->save();

        $this->calculate_profile_completed_percentage($user->id, 'signup-step-1'); //Signup step 1 completes
        UserProfile::where('user_id', $user->id)->update([
            'completed_steps'=> 1,
        ]);

        $full_name = $user->first_name.' '.$user->last_name;
        $message = 'Your account verification has successfully completed. Now you can continue and complete your profile.';
        Mail::to($user->email)->send(new RegistrationSuccess($user->email, $full_name, $message));

        return $this->sendResponse([
            'token_type' => 'bearer',
            'token' => $token,
            'user' => $this->getEmployerDetails(),
            'expires_in' => config('jwt.ttl') * 60
        ], 'Your account verification has successfully done. Now you can continue and complete your profile.');
    }

    public function setupCompanyProfile(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'country_id' => 'required|integer',
            'city_id' => 'required|integer',
            'state_id' => 'required|integer',
            'address' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'pincode' => 'required|string|max:10',
            'landline' => 'nullable|string|max:20',
            'industry_id' => 'required|integer',
            'trade_license' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'vat_registration' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'description' => 'required|string',
            'web_url' => 'required|url'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $image_path = $trade_license = $vat_registration = $logo = "";
            if (request()->hasFile('profile_image')) {
                $file = request()->file('profile_image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/profile_image/'.$fileName, file_get_contents($file));
                $image_path = 'public/storage/uploads/employer/profile_image/'.$fileName;
            }
            if (request()->hasFile('trade_license')) {
                $file = request()->file('trade_license');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/trade_license/'.$fileName, file_get_contents($file));
                $trade_license = 'public/storage/uploads/employer/trade_license/'.$fileName;
            }
            if (request()->hasFile('vat_registration')) {
                $file = request()->file('vat_registration');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/vat_registration/'.$fileName, file_get_contents($file));
                $vat_registration = 'public/storage/uploads/employer/vat_registration/'.$fileName;
            }
            if (request()->hasFile('logo')) {
                $file = request()->file('logo');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/logo/'.$fileName, file_get_contents($file));
                $logo = 'public/storage/uploads/employer/logo/'.$fileName;
            }

            UserEmployer::where('user_id', $user->id)->update([
                'country_id'=> $request->country_id,
                'city_id'=> $request->city_id,
                'state_id'=> $request->state_id,
                'address'=> $request->address,
                'address_line_2'=> $request->address_line_2,
                'pincode' => $request->pincode,
                'landline'=> $request->landline,
                'industry_id'=> $request->industry_id,
                'trade_license'=> $trade_license,
                'vat_registration'=> $vat_registration,
                'logo'=> $logo,
                'description'=> $request->description,
                'web_url'=> $request->web_url
            ]);

            return $this->sendResponse($this->getUserDetails(), 'Setup company profile has successfully done.');

        } catch (\Exception $e) {
            return $this->sendError('Error', 'Sorry!! Unable to complete setup profile.');
        }
    }

}
