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
    //try {
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

        // If business_id is null or completed_steps is 1, create/update employer profile
        if (!$userEmployer->business_id || $userEmployer->completed_steps == 1) {

            // Simple employer creation without complex location lookup
            $employer = Employer::create([
                'name' => $request->company_name ?? 'Default Company',
                'description' => $request->description ?? '',
                'industry_id' => $request->industrie_id ?? 1,
                'country_id' => 1, // Default country ID
                'state_id' => 1,   // Default state ID
                'city_id' => 1,    // Default city ID
                'address' => $request->address ?? '',
                'address_line_2' => $request->address_line_2 ?? '',
                'pincode' => $request->pincode ?? '',
                'landline' => $request->landline ?? '',
                'employe_type' => $request->employe_type ?? 'company',
                'web_url' => $request->web_url ?? '',
                'status' => 0
            ]);

            // Update user employer with new business_id
            $userEmployer->update([
                'business_id' => $employer->id,
                'completed_steps' => 2
            ]);
        }

        // Prepare job data for service
        $jobData = [
            'employer_id' => $userEmployer->business_id,
            'job_type' => $request->job_type,
            'industry' => $request->industry,
            'job_category' => $request->job_category,
            'nationality' => $request->nationality,
            'open_position_number' => $request->open_position_number,
            'contract_type' => $request->contract_type,
            'min_exp_year' => $request->min_exp_year,
            'max_exp_year' => $request->max_exp_year,
            'designation' => $request->designation,
            'position_name' => $request->position_name,
            'functional_area' => $request->functional_area,
            'job_description' => $request->job_description,
            'requirement' => $request->requirement,
            'location_countries' => $request->location_countries,
            'location_cities' => $request->location_cities,
            'skill_ids' => $request->skill_ids ?? [],
            'currency' => $request->currency,
            'min_salary' => $request->min_salary,
            'max_salary' => $request->max_salary,
            'is_salary_negotiable' => $request->is_salary_negotiable ?? false,
            'posting_open_date' => $request->posting_open_date,
            'posting_close_date' => $request->posting_close_date,
            'application_through' => $request->application_through,
            'apply_on_email' => $request->apply_on_email,
            'apply_on_link' => $request->apply_on_link,
            // Walk-in fields (null for remote jobs)
            'walkin_address1' => $request->walkin_address1,
            'walkin_address2' => $request->walkin_address2,
            'walkin_country' => $request->walkin_country,
            'walkin_state' => $request->walkin_state,
            'walkin_city' => $request->walkin_city,
            'walkin_pincode' => $request->walkin_pincode,
            'walkin_latitude' => $request->walkin_latitude,
            'walkin_longitude' => $request->walkin_longitude,
            'walkin_details' => $request->walkin_details
        ];
       //dd($jobData);
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

    // } catch (\Exception $e) {
    //     \Log::error('Job posting error: ' . $e->getMessage());
    //     return $this->sendError('Error', $e->getMessage(), 500);
    // }
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
    private function validateJobPostData(array $data)
    {
        $rules = [
            // Keep string validation for job_type and gender
            'jobtype' => 'required|string|in:walk-in-jobs,remote-jobs,on-site-jobs,temp-role-jobs',
            'industry' => 'required|integer',
            'job_category' => 'nullable|integer',
            'nationality' => 'required|integer',
            'gender' => 'required|string|in:Male,Female,Others,No-Preference',
            'numberOfPositions' => 'required|integer|min:1|max:999',
            'contract_type' => 'required|string|in:Full-time,Part-time,Temporary,Internship,Freelance,Contractor',
            'minexperience' => 'required|integer|min:0|max:50',
            'maxexperience' => 'nullable|integer|min:0|max:50|gte:minexperience',
            'designation' => 'required|integer',
            'roleName' => 'required|string|max:200',
            'jobDescription' => 'nullable|string',
            'requirements' => 'nullable|string',
            'location_countries' => 'required|integer',
            'location_cities' => 'nullable|integer',
            'skill_ids' => 'nullable|array',
            'skill_ids.*' => 'integer',
            'currency' => 'nullable|string|max:10',
            'minSalary' => 'nullable|string',
            'maxSalary' => 'nullable|string',
            'salnegotiate' => 'nullable|in:yes,no',
            'openDate' => 'nullable|date',
            'closeDate' => 'nullable|date|after:openDate',
            'applyThrough' => 'required|string|in:Hireing-Jet,Apply-Email,Apply-Link',
            'applyTo' => 'nullable|string|max:255',
            'walkin_address1' => 'required_if:jobtype,walk-in-jobs|string|max:200',
            'walkin_country' => 'required_if:jobtype,walk-in-jobs|string',
            'walkin_state' => 'nullable|string',
            'walkin_city' => 'required_if:jobtype,walk-in-jobs|string',
            'walkin_pincode' => 'required_if:jobtype,walk-in-jobs|string|max:10',
        ];

        $messages = [
            'jobtype.required' => 'Job type is required',
            'jobtype.in' => 'Invalid job type selected',
            'gender.required' => 'Gender preference is required',
            'gender.in' => 'Invalid gender option selected',
            'contract_type.required' => 'Contract type is required',
            'contract_type.in' => 'Invalid contract type selected',
            'walkin_address1.required_if' => 'Walk-in address is required for walk-in jobs',
            'walkin_country.required_if' => 'Country is required for walk-in interviews',
            'walkin_city.required_if' => 'City is required for walk-in interviews',
            'walkin_pincode.required_if' => 'Pincode is required for walk-in interviews',
        ];

        return Validator::make($data, $rules, $messages);
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
