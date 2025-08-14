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

    public function checkEmail(Request $request)
    {
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
     * Create new job posting - FIXED FOR NULL VALUES AND PROPER DATA TYPES
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

            // STEP 1: Clean and sanitize request data
            $cleanedRequest = $this->sanitizeRequestData($request);

            // Validate job posting data based on database schema
            $validator = $this->validateJobPostingData($cleanedRequest);
            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Update employer profile
            $profileUpdated = $this->updateEmployerProfile($cleanedRequest, $user);
            if (!$profileUpdated) {
                return $this->sendError('Profile Error', 'Failed to update employer profile', 400);
            }

            // FIXED: Handle application_through properly - keep as string
            $applicationThrough = $cleanedRequest->get('application_through');

            // Normalize different frontend variations to database expected values
            $applicationMap = [
                'Hireing Jet' => 'Hiring Jet',
                'Hiring Jet' => 'Hiring Jet',
                'Apply To Email' => 'Apply To Email',
                'Apply-Email' => 'Apply To Email',
                'Apply To Link' => 'Apply To Link',
                'Apply-Link' => 'Apply To Link'
            ];

            $applicationThrough = $applicationMap[$applicationThrough] ?? 'Hiring Jet';

            // Handle currency - use directly from request
            $currency = $cleanedRequest->get('currency') ?: '';

            // Prepare job data matching the database schema
            $jobData = [
                'employer_id' => $userEmployer->business_id,
                'position_name' => $cleanedRequest->get('position_name'),
                'job_type' => $cleanedRequest->get('job_type'),
                'location_countries' => $cleanedRequest->get('location_countries'),
                'location_cities' => $cleanedRequest->get('location_cities'),
                'industry' => (int) $cleanedRequest->get('industry'),
                'job_category' => $cleanedRequest->get('job_category') ? (int) $cleanedRequest->get('job_category') : null,
                'nationality' => (int) $cleanedRequest->get('nationality'),
                'gender' => $cleanedRequest->get('gender'),
                'open_position_number' => (int) $cleanedRequest->get('open_position_number'),
                'contract_type' => (int) $cleanedRequest->get('contract_type'),
                'designation' => (int) $cleanedRequest->get('designation'),
                'functional_area' => $cleanedRequest->get('functional_area') ? (int) $cleanedRequest->get('functional_area') : null,
                'min_exp_year' => (int) $cleanedRequest->get('min_exp_year'),
                'max_exp_year' => (int) $cleanedRequest->get('max_exp_year'),
                'job_description' => $cleanedRequest->get('job_description'),
                'requirement' => $cleanedRequest->get('requirement'),
                'skill_ids' => $cleanedRequest->get('skill_ids', []),
                'expected_close_date' => $cleanedRequest->get('expected_close_date'),
                'currency' => $currency,
                'min_salary' => $cleanedRequest->get('min_salary') ? (float) $cleanedRequest->get('min_salary') : 0,
                'max_salary' => $cleanedRequest->get('max_salary') ? (float) $cleanedRequest->get('max_salary') : 0,
                'is_salary_negotiable' => $cleanedRequest->boolean('is_salary_negotiable') ? 1 : 0,
                'posting_open_date' => $cleanedRequest->get('posting_open_date') ?: date('Y-m-d'),
                'posting_close_date' => $cleanedRequest->get('posting_close_date') ?: date('Y-m-d', strtotime('+30 days')),
                'application_through' => $applicationThrough, // Keep as STRING
                'apply_on_email' => $cleanedRequest->get('apply_on_email'),
                'apply_on_link' => $cleanedRequest->get('apply_on_link'),
            ];

            // Handle walk-in fields - all should be null for non-walk-in jobs
            if ($cleanedRequest->get('job_type') === 'walk-in-jobs') {
                $jobData['walkin_address1'] = $cleanedRequest->get('walkin_address1');
                $jobData['walkin_address2'] = $cleanedRequest->get('walkin_address2');
                $jobData['walkin_country'] = $cleanedRequest->get('walkin_country');
                $jobData['walkin_state'] = $cleanedRequest->get('walkin_state');
                $jobData['walkin_city'] = $cleanedRequest->get('walkin_city');
                $jobData['walkin_pincode'] = $cleanedRequest->get('walkin_pincode');
                $jobData['walkin_latitude'] = $cleanedRequest->get('walkin_latitude') ? (float) $cleanedRequest->get('walkin_latitude') : null;
                $jobData['walkin_longitude'] = $cleanedRequest->get('walkin_longitude') ? (float) $cleanedRequest->get('walkin_longitude') : null;
                $jobData['walkin_details'] = $cleanedRequest->get('walkin_details');
            } else {
                // For non-walk-in jobs, set all walk-in fields to null
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
                'request_data' => $request->all(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->sendError('Error', 'An error occurred while posting the job. Please try again.', 500);
        }
    }

    /**
     * Sanitize request data - convert string "null" to actual null values
     */
    private function sanitizeRequestData(Request $request)
    {
        $data = $request->all();

        // Fields that should be converted from string "null" to actual null
        $nullableFields = [
            'functional_area',
            'job_category',
            'expected_close_date',
            'apply_on_link',
            'walkin_address1',
            'walkin_address2',
            'walkin_country',
            'walkin_state',
            'walkin_city',
            'walkin_pincode',
            'walkin_latitude',
            'walkin_longitude',
            'walkin_details',
            'address_line_2',
            'landline'
        ];

        // Convert string "null" to actual null
        foreach ($nullableFields as $field) {
            if (isset($data[$field]) && ($data[$field] === 'null' || $data[$field] === '' || $data[$field] === 'undefined')) {
                $data[$field] = null;
            }
        }

        // Handle empty strings that should be null
        foreach ($data as $key => $value) {
            if ($value === '' || $value === 'null' || $value === 'undefined') {
                $data[$key] = null;
            }
        }

        // Create new request with cleaned data
        $cleanedRequest = new Request($data);
        $cleanedRequest->setUserResolver($request->getUserResolver());
        $cleanedRequest->setRouteResolver($request->getRouteResolver());

        return $cleanedRequest;
    }

    /**
     * Convert currency to integer ID
     */
    private function getCurrencyId($currency)
    {
        // If it's already numeric, just return as integer
        if (is_numeric($currency)) {
            return (int) $currency;
        }

        // If currency is null or empty, return default currency ID
        if (empty($currency) || $currency === 'null') {
            return 1; // Default currency ID based on your SQL example
        }

        // If it's still not numeric, return default
        return 1;
    }

    /**
     * Validation based on exact database schema with comprehensive error messages
     */
    private function validateJobPostingData($request)
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
            'contract_type' => 'required|integer|in:1,2,3,4,5,6',
            'designation' => 'required|integer|min:1',
            'functional_area' => 'nullable|integer|min:1',
            'min_exp_year' => 'required|integer|min:0|max:50',
            'max_exp_year' => 'required|integer|min:0|max:50|gte:min_exp_year',
            'job_description' => 'required|string|min:10',
            'requirement' => 'nullable|string',
            'skill_ids' => 'nullable|array',
            'skill_ids.*' => 'integer|min:1',
            'expected_close_date' => 'nullable|date|after_or_equal:today',
            'currency' => 'required', // Can be string or integer now
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
            'position_name.required' => 'Position name is required',
            'job_type.required' => 'Job type is required',
            'location_countries.required' => 'Location country is required',
            'industry.required' => 'Industry is required',
            'nationality.required' => 'Nationality is required',
            'gender.required' => 'Gender preference is required',
            'open_position_number.required' => 'Number of positions is required',
            'contract_type.required' => 'Contract type is required',
            'designation.required' => 'Designation is required',
            'min_exp_year.required' => 'Minimum experience is required',
            'max_exp_year.required' => 'Maximum experience is required',
            'max_exp_year.gte' => 'Maximum experience must be >= minimum experience',
            'job_description.required' => 'Job description is required',
            'job_description.min' => 'Job description too short',
            'currency.required' => 'Currency is required',
            'application_through.required' => 'Application method is required',
            'apply_on_email.required_if' => 'Email required for email application',
            'apply_on_email.email' => 'Invalid email address',
            'apply_on_link.required_if' => 'URL required for link application',
            'apply_on_link.url' => 'Invalid URL',
            'company_name.required' => 'Company name is required',
            'industrie_id.required' => 'Company industry is required',
            'country.required' => 'Country is required',
            'address.required' => 'Address is required',
            'pincode.required' => 'Pincode is required',
            'no_of_employee.required' => 'Number of employees is required',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Update employer profile - FIXED NULL HANDLING
     */
    public function updateEmployerProfile($request, User $user)
    {
        try {
            // Handle file uploads with a cleaner approach
            $fileFields = ['profile_image', 'trade_license', 'vat_registration', 'logo'];
            $uploadedFiles = [];

            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $uploadedFiles[$field] = $this->handleFileUpload($request->file($field), $field);
                }
            }

            // Get location IDs
            $country_id = $this->safeGetLocationId('country', $request->get('country'));
            $state_id = $this->safeGetLocationId('state', $request->get('state'), $country_id);
            $city_id = $this->safeGetLocationId('city', $request->get('city'), $country_id);

            // Create new employer/company profile
            $employer = new Employer();
            $employer->name = $request->get('company_name') ?: '';
            $employer->logo = $uploadedFiles['logo'] ?? ''; // Use empty string instead of null
            $employer->description = $request->get('description') ?: '';
            $employer->industry_id = $request->get('industrie_id');
            $employer->country_id = $country_id;
            $employer->state_id = $state_id;
            $employer->city_id = $city_id;
            $employer->address = $request->get('address') ?: '';
            $employer->address_line_2 = $request->get('address_line_2') ?: '';
            $employer->pincode = $request->get('pincode') ?: '';
            $employer->landline = $request->get('landline') ?: '';
            $employer->trade_license = $uploadedFiles['trade_license'] ?? '';
            $employer->vat_registration = $uploadedFiles['vat_registration'] ?? '';
            $employer->employe_type = $request->get('employe_type') ?: 'company';
            $employer->web_url = $request->get('web_url') ?: '';
            $employer->no_of_employee = $request->get('no_of_employee') ?: 1;
            $employer->status = 0;
            $employer->save();

            if ($employer && $employer->id) {
                $updateData = [
                    'country_id' => $country_id,
                    'city_id' => $city_id,
                    'state_id' => $state_id,
                    'address' => $request->get('address') ?: '',
                    'address_line_2' => $request->get('address_line_2') ?: '',
                    'pincode' => $request->get('pincode') ?: '',
                    'landline' => $request->get('landline') ?: '',
                    'industrie_id' => $request->get('industrie_id'),
                    'description' => $request->get('description') ?: '',
                    'business_id' => $employer->id,
                    'web_url' => $request->get('web_url') ?: '',
                    'employe_type' => $request->get('employe_type') ?: 'company',
                    'completed_steps' => 2,
                ];

                // Add uploaded files to update data
                foreach ($uploadedFiles as $field => $path) {
                    $updateData[$field] = $path;
                }

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
     * Handle file upload for employer profile
     */
    private function handleFileUpload($file, $fieldName)
    {
        $fileName = md5($file->getClientOriginalName() . '_' . time()) . "." . $file->getClientOriginalExtension();
        $path = "uploads/employer/{$fieldName}/" . $fileName;

        Storage::disk('public')->put($path, file_get_contents($file));

        return 'public/storage/' . $path;
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

    /**
     * Prepare job data - KEEP ORIGINAL FORMAT FOR BACKWARD COMPATIBILITY
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
}
