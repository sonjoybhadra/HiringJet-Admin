<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use App\Services\JobPostingService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Validator;

class EmployerPostJobController extends BaseApiController
{
    protected $jobService;
    public function __construct(JobPostingService $jobService)
    {
        $this->jobService = $jobService;
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
            // Validate job posting data
            $jobValidator = $this->validateJobPostData($request->all());
            if ($jobValidator->fails()) {
                return $this->sendError('Validation Error', $jobValidator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $jobData = $request->all();
            $jobData['application_through'] = ucwords(str_replace('-', ' ', $jobData['application_through']));
            $jobData['employer_id'] = auth()->user()->id; // Assign authenticated user's ID as employer_id

            $jobService = new JobPostingService();
            $result = $jobService->createJobPost(
                $jobData, // Pass modified data with employer_id
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
     * Validate job posting data - Fixed job_type validation
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateJobPostData(array $data)
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

        return Validator::make($data, $rules, $messages);
    }

}
