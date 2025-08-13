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
     * Create new job posting - SIMPLIFIED WITHOUT CONVERSIONS
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postJobComplete(Request $request)
{
    try {
        // Check authentication first
        $user = User::find(auth()->user()->id);
        if (!$user) {
            return $this->sendError('Unauthorized', 'Please login first', 401);
        }

        // Get or create employer details
        $userEmployer = UserEmployer::where('user_id', $user->id)->first();
        if (!$userEmployer) {
            return $this->sendError('Profile Error', 'Please complete your profile first', 400);
        }

        // Validate job posting data based on database schema
        $validator = $this->validateJobPostingData($request);
        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Update employer profile
        $profileUpdated = $this->updateEmployerProfile($request, $user);
        if (!$profileUpdated) {
            return $this->sendError('Profile Error', 'Failed to update employer profile', 400);
        }

        // Map application method to database format
        $applicationMap = [
            'Hireing Jet' => 'Hireing Jet',
            'Apply To Email' => 'Apply To Email',
            'Apply-Email' => 'Apply To Email',
            'Apply To Link' => 'Apply To Link',
            'Apply-Link' => 'Apply To Link'
        ];

        $applicationThrough = $applicationMap[$request->application_through] ?? 'Apply To Email';

        // Prepare job data matching the database schema
        $jobData = [
            'employer_id' => $userEmployer->business_id,
            'position_name' => $request->position_name,
            'job_type' => $request->job_type,
            'location_countries' => $request->location_countries, // JSON array
            'location_cities' => $request->location_cities, // JSON array
            'industry' => (int) $request->industry,
            'job_category' => $request->job_category ? (int) $request->job_category : null,
            'nationality' => (int) $request->nationality,
            'gender' => $request->gender,
            'open_position_number' => (int) $request->open_position_number,
            'contract_type' => (int) $request->contract_type,
            'designation' => (int) $request->designation,
            'functional_area' => $request->functional_area ? (int) $request->functional_area : null,
            'min_exp_year' => (int) $request->min_exp_year,
            'max_exp_year' => (int) $request->max_exp_year,
            'job_description' => $request->job_description,
            'requirement' => $request->requirement,
            'skill_ids' => $request->skill_ids ?? [], // JSON array
            'expected_close_date' => $request->expected_close_date,
            'currency' => (int) $request->currency,
            'min_salary' => $request->min_salary ? (float) $request->min_salary : 0,
            'max_salary' => $request->max_salary ? (float) $request->max_salary : 0,
            'is_salary_negotiable' => $request->boolean('is_salary_negotiable') ? 1 : 0,
            'posting_open_date' => $request->posting_open_date ?: date('Y-m-d'),
            'posting_close_date' => $request->posting_close_date ?: date('Y-m-d', strtotime('+30 days')),
            'application_through' => $applicationThrough,
            'apply_on_email' => $request->apply_on_email,
            'apply_on_link' => $request->apply_on_link,
        ];

        // Add walk-in fields only if job type is walk-in-jobs, otherwise set to null
        if ($request->job_type === 'walk-in-jobs') {
            $jobData['walkin_address1'] = $request->walkin_address1;
            $jobData['walkin_address2'] = $request->walkin_address2;
            $jobData['walkin_country'] = $request->walkin_country;
            $jobData['walkin_state'] = $request->walkin_state;
            $jobData['walkin_city'] = $request->walkin_city;
            $jobData['walkin_pincode'] = $request->walkin_pincode;
            $jobData['walkin_latitude'] = $request->walkin_latitude ? (float) $request->walkin_latitude : null;
            $jobData['walkin_longitude'] = $request->walkin_longitude ? (float) $request->walkin_longitude : null;
            $jobData['walkin_details'] = $request->walkin_details;
        } else {
            $jobData['walkin_address1'] = null;
            $jobData['walkin_address2'] = null;
            $jobData['walkin_country'] = null;
            $jobData['walkin_state'] = null;
            $jobData['walkin_city'] = null;
            $jobData['walkin_pincode'] = null;
            $jobData['walkin_latitude'] = null;
            $jobData['walkin_longitude'] = null;
            $jobData['walkin_details'] = null;
        }

        // Call job service
        $result = $this->jobService->createJobPost(
            $jobData,
            $user->id,
            $user->email,
            $user->first_name . ' ' . $user->last_name,
            $request->ip()
        );

        if ($result['success']) {
            return $this->sendResponse([
                'job_id' => $result['job_id'],
                'job_number' => $result['job_number']
            ], 'Job posted successfully');
        } else {
            return $this->sendError('Job Creation Failed', $result['message'], 400);
        }

    } catch (\Exception $e) {
        \Log::error('Job posting error: ' . $e->getMessage(), [
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);
        return $this->sendError('Error', 'An error occurred while posting the job. Please try again.', 500);
    }
}

    /**
     * Prepare job data - KEEP ORIGINAL FORMAT
     */
    private function prepareJobData(array $data): array
    {
        $prepared = $data;

        // Extract contract type value if it's an array from Select component
        if (isset($data['contractType']) && is_array($data['contractType']) && !empty($data['contractType'])) {
            $prepared['contract_type'] = $data['contractType'][0]['value'] ?? $data['contractType'][0];
        }

        // Convert select component objects to simple values
        $selectFields = [
            'positionName' => 'designation',
            'locationCountry' => 'location_countries',
            'locationCity' => 'location_cities',
            'industryValue' => 'industry',
            'jobCategory' => 'job_category',
            'nationalityValue' => 'nationality',
            'companyIndustry' => 'industrie_id',
            'contactPersonDesignation' => 'contact_person_designation',
            'currency' => 'currency'
        ];

        foreach ($selectFields as $frontendKey => $backendKey) {
            if (isset($data[$frontendKey]) && is_array($data[$frontendKey]) && isset($data[$frontendKey]['value'])) {
                $prepared[$backendKey] = $data[$frontendKey]['value'];
            }
        }

        // Handle skills array
        if (isset($data['skills']) && is_array($data['skills'])) {
            $prepared['skill_ids'] = array_column($data['skills'], 'value');
        }

        // Set default dates if empty
        if (empty($prepared['posting_open_date'])) {
            $prepared['posting_open_date'] = date('Y-m-d');
        }
        if (empty($prepared['posting_close_date'])) {
            $prepared['posting_close_date'] = date('Y-m-d', strtotime('+30 days'));
        }

        // Map application method to database format
        $applicationMap = [
            'Hireing-Jet' => 1,
            'Apply-Email' => 2,
            'Apply-Link' => 3
        ];

        if (isset($data['applyThrough']) && isset($applicationMap[$data['applyThrough']])) {
            $prepared['application_through'] = $applicationMap[$data['applyThrough']];
        }

        // Handle walk-in location data for walk-in jobs
        if ($data['jobtype'] === 'walk-in-jobs') {
            $prepared = $this->handleWalkInLocationData($prepared, $data);
        }

        return $prepared;
    }

    /**
     * Handle walk-in location data
     */
    private function handleWalkInLocationData(array $prepared, array $data): array
    {
        // Use company address if same address checkbox is checked
        if (!empty($data['sameAsCompanyAddress']) && $data['sameAsCompanyAddress'] === true) {
            $prepared['walkin_address1'] = $data['companyAddress'] ?? '';
            $prepared['walkin_address2'] = '';
            $prepared['walkin_country'] = $data['companyCountry'] ?? '';
            $prepared['walkin_state'] = $data['companyState'] ?? '';
            $prepared['walkin_city'] = $data['companyCity'] ?? '';
            $prepared['walkin_pincode'] = $data['companyZipCode'] ?? '';
        } else {
            // Use separate walk-in address
            $prepared['walkin_address1'] = $data['walkInAddress1'] ?? '';
            $prepared['walkin_address2'] = $data['walkInAddress2'] ?? '';
            $prepared['walkin_country'] = $data['walkInCountry'] ?? '';
            $prepared['walkin_state'] = $data['walkInState'] ?? '';
            $prepared['walkin_city'] = $data['walkInCity'] ?? '';
            $prepared['walkin_pincode'] = $data['walkInPincode'] ?? '';
        }

        return $prepared;
    }

    /**
     * Validate job posting data - STRING VALIDATION
     */
 /**
 * Validation based on exact database schema from SQL insert
 */
private function validateJobPostingData(Request $request)
{
    $rules = [
        // Required fields matching database schema
        'position_name' => 'required|string|max:255',
        'job_type' => 'required|string|in:walk-in-jobs,remote-jobs,on-site-jobs,temp-role-jobs',
        'location_countries' => 'required', // JSON array or single integer
        'location_cities' => 'nullable', // JSON array or single integer, can be null
        'industry' => 'required|integer|min:1',
        'job_category' => 'nullable|integer|min:1',
        'nationality' => 'required|integer|min:1',
        'gender' => 'required|string|in:Male,Female,Others,No-Preference',
        'open_position_number' => 'required|integer|min:1|max:999',
        'contract_type' => 'required|integer|in:1,2,3,4,5,6', // Based on your mapping
        'designation' => 'required|integer|min:1',
        'functional_area' => 'nullable|integer|min:1',
        'min_exp_year' => 'required|integer|min:0|max:50',
        'max_exp_year' => 'required|integer|min:0|max:50|gte:min_exp_year',
        'job_description' => 'required|string|min:10',
        'requirement' => 'nullable|string',
        'skill_ids' => 'nullable|array',
        'skill_ids.*' => 'integer|min:1',
        'expected_close_date' => 'nullable|date|after_or_equal:today',
        'currency' => 'required|integer|min:1', // Currency ID, not string
        'min_salary' => 'nullable|numeric|min:0',
        'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
        'is_salary_negotiable' => 'nullable|boolean',
        'posting_open_date' => 'nullable|date|after_or_equal:today',
        'posting_close_date' => 'nullable|date|after:posting_open_date',
        'application_through' => 'required|string|in:Hireing Jet,Apply To Email,Apply-Email,Apply To Link,Apply-Link',
        'apply_on_email' => 'required_if:application_through,Apply To Email,Apply-Email|nullable|email|max:255',
        'apply_on_link' => 'required_if:application_through,Apply To Link,Apply-Link|nullable|url|max:500',

        // Walk-in specific validation (all nullable as they can be NULL in database)
        'walkin_address1' => 'nullable|string|max:255',
        'walkin_address2' => 'nullable|string|max:255',
        'walkin_country' => 'nullable|string|max:100',
        'walkin_state' => 'nullable|string|max:100',
        'walkin_city' => 'nullable|string|max:100',
        'walkin_pincode' => 'nullable|string|max:20',
        'walkin_latitude' => 'nullable|numeric|between:-90,90',
        'walkin_longitude' => 'nullable|numeric|between:-180,180',
        'walkin_details' => 'nullable|string|max:1000',

        // Company profile fields (for updateEmployerProfile)
        'company_name' => 'sometimes|required|string|max:255',
        'description' => 'nullable|string|max:2000',
        'industrie_id' => 'sometimes|required|integer|min:1',
        'country' => 'sometimes|required|string|max:100',
        'state' => 'nullable|string|max:100',
        'city' => 'nullable|string|max:100',
        'address' => 'sometimes|required|string|max:255',
        'address_line_2' => 'nullable|string|max:255',
        'pincode' => 'sometimes|required|string|max:20',
        'landline' => 'nullable|string|max:20',
        'web_url' => 'nullable|url|max:255',
        'no_of_employee' => 'sometimes|required|integer|min:1',

    ];

    $messages = [
        // Core job posting messages
        'position_name.required' => 'Position name is required',
        'position_name.max' => 'Position name cannot exceed 255 characters',
        'job_type.required' => 'Job type is required',
        'job_type.in' => 'Invalid job type. Choose from: walk-in-jobs, remote-jobs, on-site-jobs, temp-role-jobs',
        'location_countries.required' => 'Job location country is required',
        'industry.required' => 'Industry is required',
        'industry.integer' => 'Industry must be a valid selection',
        'nationality.required' => 'Nationality preference is required',
        'nationality.integer' => 'Nationality must be a valid selection',
        'gender.required' => 'Gender preference is required',
        'gender.in' => 'Gender must be one of: Male, Female, Others, No-Preference',
        'open_position_number.required' => 'Number of open positions is required',
        'open_position_number.min' => 'At least 1 position must be available',
        'open_position_number.max' => 'Maximum 999 positions allowed',
        'contract_type.required' => 'Contract type is required',
        'contract_type.integer' => 'Contract type must be a valid selection',
        'contract_type.in' => 'Invalid contract type selected',
        'designation.required' => 'Designation is required',
        'designation.integer' => 'Designation must be a valid selection',
        'min_exp_year.required' => 'Minimum experience is required',
        'min_exp_year.integer' => 'Minimum experience must be a number',
        'min_exp_year.min' => 'Minimum experience cannot be negative',
        'min_exp_year.max' => 'Minimum experience cannot exceed 50 years',
        'max_exp_year.required' => 'Maximum experience is required',
        'max_exp_year.gte' => 'Maximum experience must be greater than or equal to minimum experience',
        'job_description.required' => 'Job description is required',
        'job_description.min' => 'Job description must be at least 10 characters',
        'currency.required' => 'Currency is required',
        'currency.integer' => 'Currency must be a valid selection',
        'max_salary.gte' => 'Maximum salary must be greater than or equal to minimum salary',
        'expected_close_date.after_or_equal' => 'Expected close date cannot be in the past',
        'posting_open_date.after_or_equal' => 'Posting open date cannot be in the past',
        'posting_close_date.after' => 'Posting close date must be after the open date',
        'application_through.required' => 'Application method is required',
        'application_through.in' => 'Invalid application method selected',
        'apply_on_email.required_if' => 'Email is required when application method is "Apply To Email"',
        'apply_on_email.email' => 'Please provide a valid email address',
        'apply_on_link.required_if' => 'Application link is required when application method is "Apply To Link"',
        'apply_on_link.url' => 'Please provide a valid URL',
        'walkin_latitude.between' => 'Latitude must be between -90 and 90',
        'walkin_longitude.between' => 'Longitude must be between -180 and 180',

        // Company profile messages
        'company_name.required' => 'Company name is required',
        'industrie_id.required' => 'Company industry is required',
        'country.required' => 'Company country is required',
        'address.required' => 'Company address is required',
        'pincode.required' => 'Company pincode is required',
        'no_of_employee.required' => 'Number of employees is required',
    ];

    return Validator::make($request->all(), $rules, $messages);
}

    /**
     * Update employer profile - SIMPLIFIED
     */
    public function updateEmployerProfile(Request $request, User $user)
    {
        try {
            // Handle file uploads (same as before)
            $profile_image = $trade_license = $vat_registration = $logo = "";

            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $fileName = md5($file->getClientOriginalName() . '_' . time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/profile_image/' . $fileName, file_get_contents($file));
                $profile_image = 'public/storage/uploads/employer/profile_image/' . $fileName;
            }

            if ($request->hasFile('trade_license')) {
                $file = $request->file('trade_license');
                $fileName = md5($file->getClientOriginalName() . '_' . time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/trade_license/' . $fileName, file_get_contents($file));
                $trade_license = 'public/storage/uploads/employer/trade_license/' . $fileName;
            }

            if ($request->hasFile('vat_registration')) {
                $file = $request->file('vat_registration');
                $fileName = md5($file->getClientOriginalName() . '_' . time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/vat_registration/' . $fileName, file_get_contents($file));
                $vat_registration = 'public/storage/uploads/employer/vat_registration/' . $fileName;
            }

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $fileName = md5($file->getClientOriginalName() . '_' . time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/logo/' . $fileName, file_get_contents($file));
                $logo = 'public/storage/uploads/employer/logo/' . $fileName;
            }

            // Get location IDs - keep your existing logic
            $country_id = $this->safeGetLocationId('country', $request->country);
            $state_id = $this->safeGetLocationId('state', $request->state, $country_id);
            $city_id = $this->safeGetLocationId('city', $request->city, $country_id);

            // Create new employer/company profile
            $employer = new Employer();
            $employer->name = $request->company_name ?? '';
            $employer->logo = $logo ?: null;
            $employer->description = $request->description ?? '';
            $employer->industry_id = $request->industrie_id;
            $employer->country_id = $country_id;
            $employer->state_id = $state_id;
            $employer->city_id = $city_id;
            $employer->address = $request->address ?? '';
            $employer->address_line_2 = $request->address_line_2 ?? '';
            $employer->pincode = $request->pincode ?? '';
            $employer->landline = $request->landline ?? '';
            $employer->trade_license = $trade_license ?: null;
            $employer->vat_registration = $vat_registration ?: null;
            $employer->employe_type = $request->employe_type ?? 'company';
            $employer->web_url = $request->web_url ?? '';
            $employer->no_of_employee = $request->no_of_employee ?? 1;
            $employer->status = 0;
            $employer->save();

            if ($employer && $employer->id) {
                $updateData = [
                    'country_id' => $country_id,
                    'city_id' => $city_id,
                    'state_id' => $state_id,
                    'address' => $request->address ?? '',
                    'address_line_2' => $request->address_line_2 ?? '',
                    'pincode' => $request->pincode ?? '',
                    'landline' => $request->landline ?? '',
                    'industrie_id' => $request->industrie_id,
                    'description' => $request->description ?? '',
                    'business_id' => $employer->id,
                    'web_url' => $request->web_url ?? '',
                    'employe_type' => $request->employe_type ?? 'company',
                    'completed_steps' => 2,
                ];

                if ($profile_image) $updateData['profile_image'] = $profile_image;
                if ($trade_license) $updateData['trade_license'] = $trade_license;
                if ($vat_registration) $updateData['vat_registration'] = $vat_registration;
                if ($logo) $updateData['logo'] = $logo;

                $user_employer = UserEmployer::where('user_id', $user->id)->update($updateData);
                return $user_employer ? true : false;
            }

            return false;

        } catch (\Exception $e) {
            \Log::error("updateEmployerProfile Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Safe location ID getter - keep your existing implementation
     */
    private function safeGetLocationId($type, $name, $countryId = null)
    {
        try {
            if (empty($name)) {
                \Log::warning("Empty {$type} name provided");
                return 1;
            }

            switch ($type) {
                case 'country':
                    $model = new Country();
                    $result = $model->getCountryId($name);
                    break;

                case 'state':
                    if (!$countryId) {
                        \Log::warning("No country ID provided for state lookup");
                        return 1;
                    }
                    $model = new State();
                    $result = $model->getStateId($name, $countryId);
                    break;

                case 'city':
                    if (!$countryId) {
                        \Log::warning("No country ID provided for city lookup");
                        return 1;
                    }
                    $model = new City();
                    $result = $model->getCityId($name, $countryId);
                    break;

                default:
                    \Log::error("Unknown location type: {$type}");
                    return 1;
            }

            if ($result === null || $result === false || !is_numeric($result) || $result <= 0) {
                \Log::error("Invalid {$type} ID returned", [
                    'name' => $name,
                    'country_id' => $countryId,
                    'result' => $result
                ]);
                return 1;
            }

            return (int) $result;

        } catch (\Exception $e) {
            \Log::error("Error getting {$type} ID: " . $e->getMessage(), [
                'name' => $name,
                'country_id' => $countryId
            ]);
            return 1;
        }
    }
}
