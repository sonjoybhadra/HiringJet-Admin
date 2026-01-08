<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

use App\Mail\SignupOtp;
use App\Mail\NotificationEmail;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserAccountSetting;

class AccountSettingsController extends BaseApiController
{
    private $otp_validation_time;
    public function __construct()
    {
        $this->otp_validation_time = env('OTP_VALIDATION_DURATION_MINUTES');
    }

    private function fetchAccountSettings($key = []){
        $sql = UserAccountSetting::where('user_id', auth()->user()->id);
        if(!empty($key)){
            $sql->whereIn('key', $key);
        }

        return $sql->get();
    }
    /**
     * Get account settings.
    */
    public function getAccountSettingsDetails(Request $request)
    {
        $sql = UserAccountSetting::where('user_id', auth()->user()->id);
        if($request->key){
            $sql->where('key', $request->key);
        }
        try {
            return $this->sendResponse(
                $this->fetchAccountSettings(),
                'User account settings details'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Post @params
    */
    public function postActivelyLookingFor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string',
            'options' => 'required_if:value,1|array',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $has_data = UserAccountSetting::where('user_id', auth()->user()->id)
                                    ->where('key', 'actively-looking-for')
                                    ->first();
            $post_data = [
                'user_id'=> auth()->user()->id,
                'key'=> 'actively-looking-for',
                'value'=> $request->value
            ];
            if($has_data){
                UserAccountSetting::where('id', $has_data->id)->update($post_data);
            }else{
                UserAccountSetting::create($post_data);
            }
            if($request->value == 1 && !empty($request->options)){
                $post_data = [
                    'user_id'=> auth()->user()->id,
                    'key'=> 'actively-looking-for-options',
                    'value'=> json_encode($request->options)
                ];

                $has_data = UserAccountSetting::where('user_id', auth()->user()->id)
                                        ->where('key', 'actively-looking-for-options')
                                        ->first();
                if($has_data){
                    UserAccountSetting::where('id', $has_data->id)->update($post_data);
                }else{
                    UserAccountSetting::create($post_data);
                }
            }
            return $this->sendResponse(
                $this->fetchAccountSettings(['actively-looking-for', 'actively-looking-for-options']),
                'Account settings updated successfuilly'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Post key list
     * account-deactive, recommended-job, career-news-&-update, promotion-offer, premium-service
    */

    public function postAccountSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'value' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $has_data = UserAccountSetting::where('user_id', auth()->user()->id)
                                    ->where('key', $request->key)
                                    ->first();
        $post_data = [
            'user_id'=> auth()->user()->id,
            'key'=> $request->key,
            'value'=> $request->value
        ];
        if($has_data){
            UserAccountSetting::where('id', $has_data->id)->update($post_data);
        }else{
            UserAccountSetting::create($post_data);
        }
        try {
            return $this->sendResponse(
                $this->fetchAccountSettings([$request->key]),
                'Settings updated successfuilly'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function postHideMyProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|array'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $has_data = UserAccountSetting::where('user_id', auth()->user()->id)
                                    ->where('key', 'hide-my-profile')
                                    ->first();
            $post_data = [
                'user_id'=> auth()->user()->id,
                'key'=> 'hide-my-profile',
                'value'=> json_encode($request->value)
            ];
            if($has_data){
                UserAccountSetting::where('id', $has_data->id)->update($post_data);
            }else{
                UserAccountSetting::create($post_data);
            }
            return $this->sendResponse(
                $this->fetchAccountSettings(['hide-my-profile']),
                'Account settings updated successfuilly'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Resend password with validity 10 minutes.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function sendVerificationOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user = User::find(auth()->user()->id);

        $otp = mt_rand(1111, 9999);
        $otp_mail_hash = base64_encode($otp);

        $user->remember_token = $otp_mail_hash;
        $user->email_verified_at = date('Y-m-d H:i:s', strtotime('+'.$this->otp_validation_time.' minutes'));
        $user->save();

        $full_name = $user->first_name.' '.$user->last_name;
        $message = 'Change email request verification OTP has sent. Please verify activation OTP.';
        Mail::to($request->email)->send(new SignupOtp($full_name, $otp, $message, 'Email Change OTP'));

        return $this->sendResponse([
            'otp'=> $otp,
            'email'=> $request->email
        ], 'Change email request OTP send successfully.');
    }

    /**
     * OTP verification to complete the process.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function verificationOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|min:4|max:4',
            'update_email'=> 'required|boolean'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try{
            $user = User::where('email', auth()->user()->email)
                            ->where('remember_token',  base64_encode($request->otp))
                            ->first();

            if(!$user){
                return $this->sendError('Error', 'Request email is not found Or OTP not matched.', Response::HTTP_UNAUTHORIZED);
            }

            $current_dt = date('Y-m-d H:i:s');
            if($current_dt > $user->email_verified_at ){
                return $this->sendError('Warning', 'OTP validation time expired.', Response::HTTP_UNAUTHORIZED);
            }

            if($request->update_email == 1){
                $user_obj = User::find($user->id);
                $user_obj->email = $request->email;
                $user_obj->remember_token = '';
                $user_obj->email_verified_at = date('Y-m-d H:i:s');
                $user_obj->save();

                UserProfile::where('user_id', $user->id)->update([
                    'email'=> $request->email,
                ]);

                $full_name = auth()->user()->first_name.' '.auth()->user()->last_name;
                Mail::to(auth()->user()->email)->send(new NotificationEmail('Email updated successfully done.', $full_name, 'Your email '.$request->email.' has been updated successfully.'));

                return $this->sendResponse($this->getUserDetails(), 'Email update successfully done.');
            }else{
                return $this->sendResponse([], 'OTP verification successfully done.');
            }
        }catch (\Exception $e) {
            return $this->sendError('Error', 'Sorry!! Unable to process right now.');
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'existing_password' => 'required|min:6',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $credentials = array(
            'email' => auth()->user()->email,
            'password' => $request->existing_password,
            'status'=> 1
        );
        $credentials['role_id'] = env('JOB_SEEKER_ROLE_ID');

        if (! $token = auth('api')->attempt($credentials)) {
            return $this->sendError('Current Password Error', 'Current Password not matched', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        User::where('id', Auth()->user()->id)
                ->update([
                    'password' => Hash::make($request->password)
                ]);

        $full_name = auth()->user()->first_name.' '.auth()->user()->last_name;
        Mail::to(auth()->user()->email)->send(new NotificationEmail('Password updated successfully done.', $full_name, 'Your password has been updated successfully. New password is: '.$request->password));

        return $this->sendResponse([
                                        'token_type' => 'bearer',
                                        'token' => $token,
                                        'user' => $this->getUserDetails(),
                                        'expires_in' => config('jwt.ttl') * 60,
                                    ], 'Password updated successfully done.');
    }

}
