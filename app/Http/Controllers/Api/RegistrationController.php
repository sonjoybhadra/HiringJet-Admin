<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserEducation;
use App\Models\UserSkill;

use App\Mail\SignupOtp;
use App\Mail\RegistrationSuccess;

class RegistrationController extends BaseApiController
{
    private $job_seeker_role, $otp_validation_time;
    public function __construct()
    {
        $this->job_seeker_role = env('JOB_SEEKER_ROLE_ID');
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
            'resume' => 'nullable|mimes:pdf,doc,docx|max:5120', // max:5120 = 5MB
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $otp = mt_rand(1111, 9999);
            $otp_mail_hash = base64_encode($otp);

            $image_path = "";
            /* if (request()->hasFile('resume')) {
                $file = request()->file('resume');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/profile_resume'.$fileName, file_get_contents($file));
                $image_path = 'storage/uploads/user/'.$fileName;
            } */

            $user_id = User::insertGetId([
                'role_id'=> $this->job_seeker_role,
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
                $full_name = $request->first_name.' '.$request->last_name;
                $message = 'Registration step 1 has successfully done. Please verify activation OTP.';
                Mail::to($request->email)->send(new SignupOtp($full_name, $otp, $message));

                return $this->sendResponse(['otp'=> $otp], 'Registration step 1 has done. Please verify OTP already send in your registered email.');
            }else{
                return $this->sendError('Error', 'Sorry!! Unable to signup.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error', 'Sorry!! Unable to signup.');
        }
    }

    /**
     * Resend password with validity 10 minutes.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function resend_otp(Request $request)
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
        Mail::to($request->email)->send(new SignupOtp($full_name, $otp, $message));

        return $this->sendResponse(['otp'=> $otp], 'OTP resend successfully. Please verify OTP already send in your registered email.');
    }

    /**
     * Registration OTP verification to complete registration.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function register_verification(Request $request)
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
                        ->first();

        if(!$user){
            return $this->sendError('Error', 'Request email is not found Or OTP not matched.', Response::HTTP_UNAUTHORIZED);
        }

        $current_dt = date('Y-m-d H:i:s');
        // if($current_dt > $user->email_verified_at ){
        //     return $this->sendError('Warning', 'OTP validation time expired.', Response::HTTP_UNAUTHORIZED);
        // }

        $user_obj = User::find($user->id);
        $user_obj->status = 1;
        $user_obj->remember_token = '';
        $user_obj->email_verified_at = date('Y-m-d H:i:s');
        $user_obj->save();

        /* $credentials = [];
        if (! $token = auth('api')->attempt($credentials)) {
            return $this->sendError('Unauthorized', 'Email or Password not matched.', Response::HTTP_UNAUTHORIZED);
        }
        auth()->shouldUse('api');
        if(auth()->user()->status == 0){
            return $this->sendError('Unauthorized', 'Your account is not active. Please contact to the admin.', Response::HTTP_UNAUTHORIZED);
        } */

        $full_name = $user->first_name.' '.$user->last_name;
        $message = 'Your account verification has successfully completed. Now you can continue and complete your profile.';
        Mail::to($user->email)->send(new RegistrationSuccess($user->email, $full_name, $message));

        return $this->sendResponse($user_obj, 'Your account verification has successfully done. Now you can continue and complete your profile.');
    }

    public function setup_profile(Request $request, $user)
    {
        /* $validator = Validator::make($request->all(), [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'resume_headline' => 'required|string|max:255',
            'currently_employed' => 'required|boolean',//yes/no
            'total_experience_years' => 'required|integer',
            'total_experience_months' => 'required|integer',
            'last_designation' => 'required|string',
            'last_employer_name' => 'required|string',
            'last_employer_location' => 'required|string',
            'working_since_from_year' => 'required|integer',
            'working_since_from_month' => 'required|integer',
            'working_since_to_year' => 'required|integer',
            'working_since_to_month' => 'required|integer',
            'current_salary' => 'required|integer',
            'current_salary_currency' => 'required|integer',
            'employer_country' => 'required|integer',
            'employer_city' => 'required|integer',

            'keyskills'=> 'nullable|required|array',

            'gender'=> 'required|string|in:male,female,other',
            'location'=> 'nullable|integer',
            'nationality'=> 'nullable|integer',
            'pasport_country'=> 'nullable|integer',
            // 'nationality'=> 'nullable|integer'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } */

        try{
            // $user = User::findOrFail($user_id);

            $image_path = "";
            if (request()->hasFile('profile_image')) {
                $file = request()->file('profile_image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/profile_image'.$fileName, file_get_contents($file));
                $image_path = 'storage/uploads/profile_image/'.$fileName;
            }

            UserProfile::insert([
                'user_id'=> $user->id,
                'first_name'=> $user->first_name,
                'last_name'=> $user->last_name,
                'email'=> $user->email,
                'country_code'=> $user->country_code,
                'phone' => $user->phone,

                'profile_image'=> $image_path,
                'gender'=> $request->gender?ucfirst($request->gender):NULL,
                'nationality_id'=> $request->nationality,
                'city_id'=> $request->location,
                'pasport_country_id'=> $request->pasport_country,

                'whats_app_number' => $request->is_whatsapp == 1 ? ($user->phone) : NULL, //[0/1]

                // 'address'=> $request->address,
                'resume_headline'=> $request->resume_headline,
                'currently_employed'=> $request->currently_employed,
                'total_experience_years'=> $request->total_experience_years,
                'total_experience_months'=> $request->total_experience_months,
                'last_designation'=> $request->last_designation,
                'last_employer_name'=> $request->last_employer_name,
                'employer_country_id'=> $request->employer_country,
                'employer_city_id'=> $request->employer_city,

                'working_since_from_year'=> $request->working_since_from_year,
                'working_since_from_month'=> $request->working_since_from_month,
                'working_since_to_year'=> $request->working_since_to_year,
                'working_since_to_month'=> $request->working_since_to_month,
                'current_salary'=> $request->current_salary,
                'current_salary_currency_id'=> $request->current_salary_currency,
            ]);

            if(!empty($request->keyskills)){
                foreach($request->keyskills as $keyskill){
                    UserSkill::insert([
                        'user_id'=> $user->id,
                        'keyskill_id'=> $keyskill,
                        'proficiency_level' => 'Beginner',
                        'is_primary'=> 1
                    ]);
                }
            }

            /* $user->status = 3;
            $user->save(); */

            return $this->sendResponse([], 'Setup profile has done. Please complete your profile now.');

        } catch (\Exception $e) {
            return $this->sendError('Error', 'Sorry!! Unable to signup.');
        }
    }

    public function complete_profile(Request $request, $user)
    {
        /* $validator = Validator::make($request->all(), [
            'profile_summery' => 'required|string',// Max:5MB
            'education' => 'required|integer',
            'course' => 'required|integer',
            'specialization' => 'required|integer',
            'university' => 'required|integer',
            'passing_year' => 'required|integer',
            'location' => 'required|integer',
            'preferred_designation' => 'nullable|array',
            'preferred_location' => 'nullable|array',
            'preferred_industry' => 'nullable|array',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } */

        try{
            // $user = User::findOrFail($user_id);

            UserProfile::where('user_id', $user->id)
                                    ->update([
                                        'profile_summery'=> $request->profile_summery,
                                        'preferred_designation' => [],
                                        'preferred_location' => [],
                                        'preferred_industry' => []
                                    ]);

            UserEducation::insertGetId([
                'user_id'=> $user->id,
                'education_id'=> $request->education,
                'course_id'=> $request->course,
                'specialization_id'=> $request->specialization,
                'location_id' => $request->location,
                'university_id'=> $request->university,
                'passing_year'=> $request->passing_year
            ]);

            /* $user->status = 1;
            $user->save(); */

            return $this->sendResponse([], 'Your profile completed successfully.');

        } catch (\Exception $e) {
            return $this->sendError('Error', 'Sorry!! Unable to signup.');
        }
    }

}
