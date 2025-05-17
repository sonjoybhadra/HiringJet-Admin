<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use Validator;
use App\Models\User;
use App\Mail\ForgotPassword;
use App\Mail\ResetPassword;

class ForgotpasswordController extends BaseApiController
{
    /**
     * Forgot password request process start here.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function forgot_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $input = $request->only(['email']);
        $user = User::where('email', $input)->first();
        if(!$user){
            return $this->sendError('Invalid Email', 'Request email is not found.', Response::HTTP_UNAUTHORIZED);
        }

        $otp = mt_rand(1111, 9999);
        $otp_mail_hash = base64_encode($otp);
        User::where('id', $user->id)
                ->update([
                    'remember_token' => $otp_mail_hash,
                    'email_verified_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
                ]);

        // $email = 'work.chayan2020@gmail.com';
        Mail::to($request->input('email'))->send(new ForgotPassword($otp));

        return $this->sendResponse([], 'Reset password OTP has sent successfully to your register email.');
    }

    /**
     * Forgot password OTP verification to ensure email and user is authenticated.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function otp_verification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user = User::where('email', $request->email)
                        ->where('remember_token',  base64_encode($request->otp))
                        ->first();

        if(!$user){
            return $this->sendError('Error', 'OTP not matched.', Response::HTTP_UNAUTHORIZED);
        }

        $current_dt = date('Y-m-d H:i:s');
        if($current_dt > $user->email_verified_at ){
            return $this->sendError('Warning', 'OTP validation time expired.', Response::HTTP_UNAUTHORIZED);
        }

        return $this->sendResponse([
            'reset_password_hash'=> base64_encode($request->otp)
        ], 'OTP verification has successfully done. Update your password.');
    }

    /**
     * Confirm Forgot Password OTP and update new password after verifying token hash.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'reset_password_hash' => 'required',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('email', $request->email)
                    ->where('remember_token',  trim($request->reset_password_hash))
                    ->first();
        try{
            User::where('id', $user->id)
                    ->update([
                        'password' => Hash::make($request->password),
                        'email_verified_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                        'remember_token' => null
                    ]);

            Mail::to($request->input('email'))->send(new ResetPassword());

            return $this->sendResponse([], 'Password update successfully done.');
        }catch (\Exception $exception) {
            return $this->sendError('Error', 'Sorry!! Something went wrong. Unable to update password.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
