<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserResume;
use App\Models\UserEducation;
use App\Models\UserSkill;
use App\Models\UserEmployment;
use App\Models\ProfileComplete;
use App\Models\UserProfileCompletedPercentage;

use App\Mail\SignupOtp;
use App\Mail\RegistrationSuccess;
use App\Models\City;
use App\Models\Designation;
use App\Models\Industry;

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
            'is_experienced' => 'required|boolean',//yes/no
            'resume' => 'nullable|mimes:pdf,doc,docx|max:5120', // max:5120 = 5MB
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $otp = mt_rand(1111, 9999);
            $otp_mail_hash = base64_encode($otp);

            $image_path = "";
            if (request()->hasFile('resume')) {
                $file = request()->file('resume');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/resume/'.$fileName, file_get_contents($file));
                $image_path = 'public/storage/uploads/user/resume/'.$fileName;
            }
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
                UserProfile::insert([
                    'user_id'=> $user_id,
                    'first_name'=> $request->first_name,
                    'last_name'=> $request->last_name,
                    'email'=> $request->email,
                    'country_code'=> $request->country_code,
                    'phone' => $request->phone,
                    'is_experienced'=> $request->is_experienced,
                    'profile_completed_percentage'=> 0,
                    'completed_steps'=> 0
                ]);

                $this->calculate_profile_completed_percentage($user_id, 'full-name'); //Full name completes
                if($request->is_whatsapp == 1){
                    $this->calculate_profile_completed_percentage($user_id, 'whatsapp'); //WhatsApp completes
                }

                if($image_path != ""){
                    UserResume::insert([
                        'user_id' => $user_id,
                        'resume' => $image_path,
                        'is_default' => 1
                    ]);
                    $this->calculate_profile_completed_percentage($user_id, 'upload-cv'); //CV Uploads completes
                }

                $full_name = $request->first_name.' '.$request->last_name;
                $message = 'Registration step 1 has successfully done. Please verify activation OTP.';
                Mail::to($request->email)->send(new SignupOtp($full_name, $otp, $message));

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
        Mail::to($request->email)->send(new SignupOtp($full_name, $otp, $message));

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
                        ->with('user_profile')
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
            'user' => $this->getUserDetails(),
            'expires_in' => config('jwt.ttl') * 60
        ], 'Your account verification has successfully done. Now you can continue and complete your profile.');
    }

    public function setupProfile(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'resume_headline' => 'required|string|max:255',
            'currently_employed' => 'required|in:1,0',//yes/no
            'total_experience_years' => 'required_if:currently_employed,1|integer',
            'total_experience_months' => 'required_if:currently_employed,1|integer',
            'last_designation' => 'required_if:currently_employed,1|string',
            'last_employer' => 'required_if:currently_employed,1|string',
            // 'last_employer_location' => 'required_if:currently_employed,1|string',
            'working_since_from_year' => 'required_if:currently_employed,1|integer',
            'working_since_from_month' => 'required_if:currently_employed,1|integer',
            'working_since_to_year' => 'required_if:currently_employed,1|integer',
            'working_since_to_month' => 'required_if:currently_employed,1|integer',
            'salary_currency' => 'required_if:currently_employed,1|integer',
            'current_salary' => 'required_if:currently_employed,1|integer',

            'keyskills'=> 'nullable|required|array',

            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'gender'=> 'nullable|string|in:male,female,other',
            'location'=> 'nullable|integer',
            'nationality'=> 'nullable|integer',
            'pasport_country'=> 'nullable|integer',
            'phone' => 'required|max:15|unique:users,phone,'.$user->id,
            'is_whatsapp'=> 'required|in:1,0',
            'whatsapp_no' => 'required_if:is_whatsapp,0|integer',
            'whatsapp_country_code' => 'required_if:is_whatsapp,0|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $image_path = "";
            if (request()->hasFile('profile_image')) {
                $file = request()->file('profile_image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/profile_image/'.$fileName, file_get_contents($file));
                $image_path = 'public/storage/uploads/user/profile_image/'.$fileName;
            }

            User::where('id', $user->id)->update([
                'first_name'=> $request->first_name,
                'last_name'=> $request->last_name,
                'phone' => $request->phone,
            ]);

            UserProfile::where('user_id', $user->id)->update([
                'first_name'=> $request->first_name,
                'last_name'=> $request->last_name,
                'country_code' => $request->country_code,
                'phone' => $request->phone,
                'profile_image'=> $image_path,
                'gender'=> $request->gender ? ucfirst($request->gender) : NULL,
                'nationality_id'=> $request->nationality,
                'country_id'=> $request->country_id,
                'city_id'=> $request->location,
                'pasport_country_id'=> $request->pasport_country,
                'whatsapp_country_code'=> $request->is_whatsapp == 1 ? $request->country_code : $request->whatsapp_country_code,
                'whatsapp_number' => $request->is_whatsapp == 1 ? $request->phone : $request->whatsapp_no, //[0/1]
                'resume_headline'=> $request->resume_headline,
                'currently_employed'=> $request->currently_employed,
                'completed_steps'=> 2,
            ]);

            if($request->currently_employed == 1){
                UserEmployment::where('user_id', $user->id)->delete();
                UserEmployment::create([
                    'user_id'=> $user->id,
                    'total_experience_years'=> $request->total_experience_years,
                    'total_experience_months'=> $request->total_experience_months,
                    'last_designation'=> $request->last_designation,
                    'employer_id'=> $request->last_employer,
                    'country_id'=> $request->employer_country,
                    'city_id'=> $request->employer_city,
                    'currency_id'=> $request->salary_currency,
                    'current_salary'=> $request->current_salary,
                    'working_since_from_year'=> $request->working_since_from_year,
                    'working_since_from_month'=> $request->working_since_from_month,
                    'working_since_to_year'=> $request->working_since_to_year,
                    'working_since_to_month'=> $request->working_since_to_month,
                    'is_current_job'=> 1,
                ]);
            }

            if($image_path != ""){
                $this->calculate_profile_completed_percentage($user->id, 'upload-photo'); //Profile photo completes
            }
            if(!empty($request->location)){
                $this->calculate_profile_completed_percentage($user->id, 'current-location'); //Current location completes
            }
            if(!empty($request->nationality)){
                $this->calculate_profile_completed_percentage($user->id, 'nationality'); //Nationality completes
            }
            if(!empty($request->gender)){
                $this->calculate_profile_completed_percentage($user->id, 'gender'); //Gender completes
            }
            if(!empty($request->is_whatsapp)){
                $this->calculate_profile_completed_percentage($user->id, 'whatsapp'); //WhatsApp completes
            }

            if(!empty($request->keyskills)){
                UserSkill::where('user_id', $user->id)->delete();
                foreach($request->keyskills as $keyskill){
                    UserSkill::insert([
                        'user_id'=> $user->id,
                        'keyskill_id'=> $keyskill,
                        'proficiency_level' => 'Beginner',
                        'is_primary'=> 1
                    ]);
                }

                $this->calculate_profile_completed_percentage($user->id, 'key-skills'); //Key skills completes
            }

            return $this->sendResponse($this->getUserDetails(), 'Setup profile has done. Please complete your profile now.');

        } catch (\Exception $e) {
            return $this->sendError('Error', 'Sorry!! Unable to complete setup profile.');
        }
    }

    public function completeProfile(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'profile_summery' => 'required|string',// Max:5MB

            'qualification' => 'required|integer',
            'course' => 'required|integer',
            'specialization' => 'required|integer',
            'university' => 'required|integer',
            'passing_year' => 'required|integer',
            'location' => 'required|integer',

            'preferred_designation' => 'nullable|array',
            'preferred_location' => 'nullable|array',
            'preferred_industry' => 'nullable|array',
            'availability' => 'nullable|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $preferred_designation = $preferred_location = $preferred_industry = [];
            if(!empty($request->preferred_designation)){
                $designations = Designation::whereIn('id', $request->preferred_designation)->get();
                if($designations->count() > 0){
                    foreach($designations as $designation){
                        $preferred_designation[] = ['id'=> $designation->id, 'name'=> $designation->name];
                    }
                }
            }
            if(!empty($request->preferred_location)){
                $locations = City::whereIn('id', $request->preferred_location)->get();
                if($locations->count() > 0){
                    foreach($locations as $location){
                        $preferred_location[] = ['id'=> $location->id, 'name'=> $location->name];
                    }
                }
            }
            if(!empty($request->preferred_industry)){
                $industrys = Industry::whereIn('id', $request->preferred_industry)->get();
                if($industrys->count() > 0){
                    foreach($industrys as $industry){
                        $preferred_industry[] = ['id'=> $industry->id, 'name'=> $industry->name];
                    }
                }
            }

            UserProfile::where('user_id', $user->id)
                        ->update([
                            'profile_summery'=> $request->profile_summery,
                            'preferred_designation' => !empty($preferred_designation) ? json_encode($preferred_designation) : NULL,
                            'preferred_location' => !empty($preferred_location) ? json_encode($preferred_location) : NULL,
                            'preferred_industry' => !empty($preferred_industry) ? json_encode($preferred_industry) : NULL,
                            'availability_id' => $request->availability,
                            'completed_steps'=> 3,
                        ]);
            if(!empty($request->profile_summery)){
                $this->calculate_profile_completed_percentage($user->id, 'profile-summary'); //Profile Summary completes
            }
            if(!empty($preferred_designation)){
                $this->calculate_profile_completed_percentage($user->id, 'desired-job'); //Education completes
            }
            if(!empty($request->qualification)){
                UserEducation::where('user_id', $user->id)->delete();
                UserEducation::create([
                    'user_id'=> $user->id,
                    'qualification_id'=> $request->qualification,
                    'course_id'=> $request->course,
                    'specialization_id'=> $request->specialization,
                    'location_id' => $request->location,
                    'university_id'=> $request->university,
                    'passing_year'=> $request->passing_year
                ]);
                $this->calculate_profile_completed_percentage($user->id, 'education'); //Education completes
            }

            return $this->sendResponse($this->getUserDetails(), 'Your profile completed successfully.');

        } catch (\Exception $e) {
            return $this->sendError('Error', 'Sorry!! Unable to complete profile.'.$e->getMessage());
        }
    }

}
