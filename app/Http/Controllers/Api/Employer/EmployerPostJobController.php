<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use App\Services\JobPostingService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Validator;

use App\Models\PostJob;
use App\Models\User;

class EmployerPostJobController extends BaseApiController
{
    protected $jobService;
    public function __construct(JobPostingService $jobService)
    {
        $this->jobService = $jobService;
    }

    /**
        * Jobs list for employers
        @response json
    */
    public function getMyPostedJobs(Request $request){
        $sql = PostJob::select('*')
                        ->with('industryRelation')
                        ->with('jobCategory')
                        ->with('nationalityRelation')
                        ->with('contractType')
                        ->with('designationRelation')
                        ->with('functionalArea')
                        ->with('applied_users');
        if(auth()->user()->parent_id > 0){
            $sql->where('employer_id', auth()->user()->user_employer_details->business_id);
        }else{
            $child_user_business_array = User::select('user_employers.business_id')
                                ->join('user_employers', 'user_employers.user_id', '=', 'users.id')
                                ->where('users.parent_id', auth()->user()->id)
                                ->get()->pluck('business_id')->toArray();

            array_push($child_user_business_array, auth()->user()->user_employer_details->business_id);

            $sql->whereIn('employer_id', $child_user_business_array);
        }

        $list = $sql->latest()->get();

        return $this->sendResponse($list, 'List of posted jobs');
    }

    /**
     * Create new job posting - matches your existing controller
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function postJob(Request $request)
    {
        try {
            // STEP 1: Clean and sanitize request data
            $cleanedRequest = $this->sanitizeRequestData($request);
            // Validate job posting data
            $validator = $this->validateJobPostData($cleanedRequest);
            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // $jobData = $request->all();
            // $jobData['application_through'] = ucwords(str_replace('-', ' ', $jobData['application_through']));
            // $jobData['expected_close_date'] = date('Y-m-d', strtotime('+1 month'));
            // $jobData['employer_id'] = auth()->user()->user_employer_details->business_id; // Assign authenticated user's ID as employer_id

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
                'employer_id' => auth()->user()->user_employer_details->business_id,
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

            //Override expected closed date by adding 1 month from today.
            $jobData['expected_close_date'] = date('Y-m-d', strtotime('+1 month'));

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

            // $jobService = new JobPostingService();
            // $result = $jobService->createJobPost(
            //     $jobData, // Pass modified data with employer_id
            //     auth()->id(),
            //     auth()->user()->email,
            //     auth()->user()->first_name . ' ' . auth()->user()->last_name,
            //     $request->ip()
            // );

            // Call job service
            $result = $this->jobService->createJobPost(
                $jobData,
                auth()->id(),
                auth()->user()->email,
                auth()->user()->first_name . ' ' . auth()->user()->last_name,
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
     * Validate job posting data - Fixed job_type validation
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateJobPostData_old($request)
    {
        $rules = [
            'position_name' => 'required|string|max:200',
            'job_type' => 'required|in:walk-in-jobs,remote-jobs,on-site-jobs,temp-role-jobs', // Fixed: removed quotes
            'location_countries' => 'nullable|array',
            'location_countries.*' => 'integer',
            'location_cities' => 'nullable|array',
            'location_cities.*' => 'integer',
            'industry' => 'required|integer',
            'job_category' => 'required|integer',
            // 'nationality' => 'required|array|min:1',
            'nationality' => 'integer',
            'gender' => 'required|string',
            'open_position_number' => 'required|integer|min:1|max:999',
            'contract_type' => 'required|integer',
            'designation' => 'required|integer',
            'min_exp_year' => 'required|integer|min:0|max:50',
            'max_exp_year' => 'nullable|integer|min:0|max:50|gte:min_exp_year',

            // 'functional_area' => 'nullable|integer',
            'job_description' => 'required|string|min:10',
            // 'brand_id' => 'nullable|integer',
            'requirement' => 'nullable|string',
            'skill_ids' => 'nullable|array',
            'skill_ids.*' => 'integer',
            'is_salary_negotiable' => 'nullable|boolean',
            'currency' => 'nullable|string|max:10',
            'min_salary' => 'nullable|string',
            'max_salary' => 'nullable|string',
            'posting_open_date' => 'required|date|after_or_equal:today',
            'posting_close_date' => 'required|date|after:posting_open_date',

            'application_through' => 'required|string|in:hiring-jet,apply-to-email,apply-to-link',
            'apply_on_email' => 'required_if:application_through,apply-to-email|email|max:100',
            'apply_on_link' => 'required_if:application_through,apply-to-link|url|max:500',

            'walkin_address1' => 'required_if:job_type,walk-in-jobs|string|max:200',
            'walkin_address2' => 'nullable|string|max:200',
            'walkin_country' => 'required_if:job_type,walk-in-jobs|string',
            'walkin_state' => 'required_if:job_type,walk-in-jobs|string',
            'walkin_city' => 'required_if:job_type,walk-in-jobs|string',
            'walkin_pincode' => 'required_if:job_type,walk-in-jobs|string|max:10',
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
            // 'brand_id.required' => 'Employer brand is required',
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

        return Validator::make($request->all(), $rules, $messages);
    }
    /**
     * Validation based on exact database schema with comprehensive error messages
     */
    private function validateJobPostData($request)
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

}
