<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use App\Services\JobPostingService;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\UserEmployer;
use App\Models\Employer;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Mail\SignupOtp;
use App\Mail\RegistrationSuccess;
use Illuminate\Http\Request;


class EmployerPostJobRegistrationController extends BaseApiController
{
    private $employer, $employer_user, $otp_validation_time;
    protected $jobService;
    public function __construct(JobPostingService $jobService)
    {
        $this->jobService = $jobService;
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
                'password'=> Hash::make('password'),
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
                    'business_id'=> 1,
                    'designation_id'=>1,
                    'completed_steps'=> 1
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
    /**
     * Create new job posting - matches your existing controller
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function postJobComplete(Request $request)
    {
        try {
            $user = User::with('user_employer_details')->findOrFail(auth()->user()->id);

            if (!$user) {
                return $this->sendError('Unauthorized', 'Please login first', Response::HTTP_UNAUTHORIZED);
            }

            // Validate job posting data
            $jobValidator = $this->validateJobPostData($request->all());
            if ($jobValidator->fails()) {
                return $this->sendError('Validation Error', $jobValidator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check if profile needs completion (step 1 = incomplete profile)
            if ($user->user_employer_details && $user->user_employer_details->completed_steps == 1) {
                // Validate profile completion fields
                $profileValidator = Validator::make($request->all(), [
                    'address' => 'required|string|max:255',
                    'country' => 'required|string',
                    'state' => 'required|string',
                    'city' => 'required|string',
                    'pincode' => 'required|string|max:10',
                    'business_id' => 'required',
                    'description' => 'required|string',
                    'industrie_id' => 'required|integer',
                    'web_url' => 'required|url',
                    'employe_type' => 'required|in:company,agency'
                ], [
                    'address.required' => 'Address is required to complete your profile',
                    'country.required' => 'Country is required',
                    'state.required' => 'State is required',
                    'city.required' => 'City is required',
                    'pincode.required' => 'Pincode is required',
                    'business_id.required' => 'Business ID is required',
                    'description.required' => 'Company description is required',
                    'industrie_id.required' => 'Industry selection is required',
                    'web_url.required' => 'Website URL is required',
                    'web_url.url' => 'Please provide a valid website URL',
                    'employe_type.required' => 'Employee type is required',
                    'employe_type.in' => 'Employee type must be either company or agency'
                ]);

                if ($profileValidator->fails()) {
                    return $this->sendError('Profile Completion Required', $profileValidator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
                }

            }

          if ($user->user_employer_details && $user->user_employer_details->completed_steps == 1) {
                // Update employer profile first
                $profileUpdated = $this->updateEmployerProfile($request, $user);
                if (!$profileUpdated) {
                    return $this->sendError('Error', 'Failed to update profile. Please try again.');
                }
                // Refresh user data after profile update
                $user = User::with('user_employer_details')->findOrFail(auth()->user()->id);
            }            // Add employer_id to request data (using authenticated user's ID)
            $jobData = $request->all();
            $jobData['employer_id'] =$user->user_employer_details->business_id; // Assign authenticated user's ID as employer_id

            $jobService = new JobPostingService();
            $result = $jobService->createJobPost(
                $jobData, // Pass modified data with employer_id
                auth()->id(),
                auth()->user()->email,
                $user->first_name . ' ' . $user->last_name,
                $request->ip()
            );

            if (!$result['success']) {
                return $this->sendError(
                    $result['message'],
                    $result['errors'] ?? $result['error'] ?? null,
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return $this->sendResponse([
                'job_id' => $result['job_id'],
                'job_number' => $result['job_number']
            ], 'Your job post has successfully done.');

        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Validate job posting data - Fixed job_type validation
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateJobPostData(array $data)
    {
        $rules = [
            'job_type' => 'required|in:walk-in-jobs,remote-jobs,on-site-jobs,temp-role-jobs', // Fixed: removed quotes
            'industry' => 'required|integer',
            'job_category' => 'required|integer',
            'nationality' => 'required|array|min:1',
            'nationality.*' => 'integer',
            'open_position_number' => 'required|integer|min:1|max:999',
            'contract_type' => 'required|integer',
            'min_exp_year' => 'required|integer|min:0|max:50',
            'max_exp_year' => 'nullable|integer|min:0|max:50|gte:min_exp_year',
            'designation' => 'required|integer',
            'position_name' => 'required_if:designation,7037|string|max:200',
            'functional_area' => 'nullable|integer',
            'job_description' => 'required|string|min:10',
            'requirement' => 'nullable|string',
            'location_countries' => 'nullable|array',
            'location_countries.*' => 'integer',
            'location_cities' => 'nullable|array',
            'location_cities.*' => 'integer',
            'skill_ids' => 'nullable|array',
            'skill_ids.*' => 'integer',
            'currency' => 'nullable|string|max:10',
            'min_salary' => 'nullable|string',
            'max_salary' => 'nullable|string',
            'is_salary_negotiable' => 'nullable|boolean',
            'posting_open_date' => 'required|date|after_or_equal:today',
            'posting_close_date' => 'required|date|after:posting_open_date',
            'application_through' => 'required|integer|in:1,2,3',
            'apply_on_email' => 'required_if:application_through,1|email|max:100',
            'apply_on_link' => 'required_if:application_through,2|url|max:500',
            'walkin_address1' => 'required_if:application_through,3|string|max:200',
            'walkin_address2' => 'nullable|string|max:200',
            'walkin_country' => 'required_if:application_through,3|integer',
            'walkin_state' => 'required_if:application_through,3|integer',
            'walkin_city' => 'required_if:application_through,3|integer',
            'walkin_pincode' => 'required_if:application_through,3|string|max:10',
            'walkin_latitude' => 'nullable|string',
            'walkin_longitude' => 'nullable|string',
            'walkin_details' => 'nullable|string',
        ];

        $messages = [
            'job_type.required' => 'Job type is required',
            'job_type.in' => 'Invalid job type selected. Must be one of: walk-in-jobs, remote-jobs, on-site-jobs, temp-role-jobs',
            'industry.required' => 'Industry is required',
            'job_category.required' => 'Job category is required',
            'nationality.required' => 'At least one nationality must be selected',
            'open_position_number.required' => 'Number of open positions is required',
            'open_position_number.min' => 'At least 1 position must be available',
            'open_position_number.max' => 'Maximum 999 positions allowed',
            'contract_type.required' => 'Contract type is required',
            'min_exp_year.required' => 'Minimum experience is required',
            'min_exp_year.min' => 'Experience cannot be negative',
            'min_exp_year.max' => 'Maximum 50 years experience allowed',
            'max_exp_year.gte' => 'Maximum experience must be greater than or equal to minimum experience',
            'designation.required' => 'Designation is required',
            'position_name.required_if' => 'Position name is required when "Others" designation is selected',
            'job_description.required' => 'Job description is required',
            'job_description.min' => 'Job description must be at least 10 characters long',
            'posting_open_date.required' => 'Job posting open date is required',
            'posting_open_date.after_or_equal' => 'Job posting open date cannot be in the past',
            'posting_close_date.required' => 'Job posting close date is required',
            'posting_close_date.after' => 'Job posting close date must be after open date',
            'application_through.required' => 'Application method is required',
            'application_through.in' => 'Invalid application method selected',
            'apply_on_email.required_if' => 'Email address is required when email application method is selected',
            'apply_on_email.email' => 'Please provide a valid email address',
            'apply_on_link.required_if' => 'Application link is required when online link method is selected',
            'apply_on_link.url' => 'Please provide a valid URL',
            'walkin_address1.required_if' => 'Walk-in address is required when walk-in method is selected',
            'walkin_country.required_if' => 'Country is required for walk-in interviews',
            'walkin_state.required_if' => 'State is required for walk-in interviews',
            'walkin_city.required_if' => 'City is required for walk-in interviews',
            'walkin_pincode.required_if' => 'Pincode is required for walk-in interviews',
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Update employer profile with file uploads - Fixed return statement
     *
     * @param Request $request
     * @param User $user
     * @return bool
     */
    public function updateEmployerProfile(Request $request, User $user)
    {
        try {
            $profile_image = $trade_license = $vat_registration = $logo = "";

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $fileName = md5($file->getClientOriginalName() . '_' . time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/profile_image/' . $fileName, file_get_contents($file));
                $profile_image = 'public/storage/uploads/employer/profile_image/' . $fileName;
            }

            // Handle trade license upload
            if ($request->hasFile('trade_license')) {
                $file = $request->file('trade_license');
                $fileName = md5($file->getClientOriginalName() . '_' . time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/trade_license/' . $fileName, file_get_contents($file));
                $trade_license = 'public/storage/uploads/employer/trade_license/' . $fileName;
            }

            // Handle VAT registration upload
            if ($request->hasFile('vat_registration')) {
                $file = $request->file('vat_registration');
                $fileName = md5($file->getClientOriginalName() . '_' . time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/vat_registration/' . $fileName, file_get_contents($file));
                $vat_registration = 'public/storage/uploads/employer/vat_registration/' . $fileName;
            }

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileName = md5($file->getClientOriginalName() . '_' . time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/logo/' . $fileName, file_get_contents($file));
                $logo = 'public/storage/uploads/employer/logo/' . $fileName;
            }

            // Get location IDs
            $city = new City();
            $country = new Country();
            $state = new State();

            $country_id = $country->getCountryId($request->country);
            $state_id = $state->getStateId($request->state, $country_id);
            $city_id = $city->getCityId($request->city, $country_id);

            // create new  employer/company profile
             $employer = new Employer();
                $employer->name = $request->company_name ?? null;
                $employer->logo = $request->logo ?? null;
                $employer->description = $request->description ?? null;
                $employer->industry_id = $request->industry_id;
                $employer->country_id = $country_id;
                $employer->state_id = $state_id;
                $employer->city_id = $city_id;
                $employer->address = $request->address;
                $employer->address_line_2 = $request->address_line_2 ?? null;
                $employer->pincode = $request->pincode;
                $employer->landline = $request->landline ?? null;
                $employer->trade_license = $request->trade_license ?? null;
                $employer->vat_registration = $request->vat_registration ?? null;
                $employer->employe_type = $request->employe_type;
                $employer->web_url = $request->web_url ?? null;
                $employer->status =0 ?? 0; // default active
                $employer->save();
            if ($employer) {
                // Update super user employer profile
                $updateData = [
                    'country_id' => $country_id,
                    'city_id' => $city_id,
                    'state_id' => $state_id,
                    'address' => $request->address,
                    'address_line_2' => $request->address_line_2,
                    'pincode' => $request->pincode,
                    'landline' => $request->landline,
                    'industrie_id' => $request->industrie_id,
                    'description' => $request->description,
                    'business_id' => $employer->id,
                    'web_url' => $request->web_url,
                    'employe_type' => $request->employe_type,
                    'completed_steps' => 2, // Mark profile as completed
                ];

                // Add file paths if uploaded
                if ($profile_image) $updateData['profile_image'] = $profile_image;
                if ($trade_license) $updateData['trade_license'] = $trade_license;
                if ($vat_registration) $updateData['vat_registration'] = $vat_registration;
                if ($logo) $updateData['logo'] = $logo;
            }


            $user_employer = UserEmployer::where('user_id', $user->id)->update($updateData);

            return $user_employer ? true : false; // Fixed: return boolean instead of undefined variable

        } catch (\Exception $e) {
            \Log::error("updateEmployerProfile Error: " . $e->getMessage());
            return false;
        }
    }
}
