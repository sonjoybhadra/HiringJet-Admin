<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserEmployment;
use App\Models\UserEmploymentSkill;

class EditEmploymentDetailsController extends BaseApiController
{
    /**
     * Get educational details.
    */
    public function getEmploymentDetails()
    {
        try {
            return $this->sendResponse(
                UserEmployment::where('user_id', auth()->user()->id)
                                ->with('employer')
                                ->with('countrie')
                                ->with('city')
                                ->with('skills')
                                ->with('notice_period')
                                ->with('industrys')
                                ->with('functional_areas')
                                ->with('park_benefits')
                                ->first()
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Post Employment details.
    */
    public function updateEmploymentDetails(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'total_experience_years' => 'required|integer',
            'total_experience_months' => 'required|integer',
            'designation' => 'required|string',
            'employer' => 'required|string',
            'industry' => 'required|integer',
            'functional_area' => 'required|integer',
            'employment_type' => 'required|integer',
            'location' => 'required|integer',
            'is_current_job'=> 'required|boolean',
            'notice_period'=> 'required|integer',
            'working_since_from_year' => 'required|integer',
            'working_since_from_month' => 'required|integer',
            'working_since_to_year' => 'required|integer',
            'working_since_to_month' => 'required|integer',
            'salary_currency' => 'required|integer',
            'current_salary' => 'required|integer',
            'skills' => 'required|array',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            if($request->is_current_job == 1){
                UserEmployment::where('user_id', auth()->user()->id)->update([
                    'is_current_job'=> 0
                ]);
            }
            UserEmployment::where('id', $id)->update([
                'total_experience_years'=> $request->total_experience_years,
                'total_experience_months'=> $request->total_experience_months,
                'last_designation'=> $request->designation,
                'employer_id'=> $request->employer,
                'employment_type'=> $request->employment_type,
                // 'country_id'=> $request->employer_country,
                'city_id'=> $request->location,
                'notice_period'=> $request->notice_period,
                'working_since_from_year'=> $request->working_since_from_year,
                'working_since_from_month'=> $request->working_since_from_month,
                'working_since_to_year'=> $request->working_since_to_year,
                'working_since_to_month'=> $request->working_since_to_month,
                'currency_id'=> $request->salary_currency,
                'current_salary'=> $request->current_salary,
                'is_current_job'=> $request->is_current_job,
            ]);

            if(!empty($request->skills)){
                UserEmploymentSkill::where('user_employment_id', $id)->delete();
                foreach($request->skills as $skill){
                    UserEmploymentSkill::insert([
                        'user_id'=> auth()->user()->id,
                        'user_employment_id'=> $id,
                        'keyskill_id'=> $skill,
                    ]);
                }
            }

            return $this->sendResponse($this->getUserDetails(), 'Professional details updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Post Employment details.
    */
    public function postEmploymentDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_experience_years' => 'required|integer',
            'total_experience_months' => 'required|integer',
            'designation' => 'required|string',
            'employer' => 'required|string',
            'industry' => 'required|integer',
            'functional_area' => 'required|integer',
            'salary_currency' => 'required|integer',
            'current_salary' => 'required|integer',
            'perk_benefits' => 'required|integer',
            'employment_type' => 'required|integer',
            'location' => 'required|integer',
            'is_current_job'=> 'required|boolean',
            'notice_period'=> 'required|integer',
            'working_since_from_year' => 'required|integer',
            'working_since_from_month' => 'required|integer',
            'working_since_to_year' => 'required|integer',
            'working_since_to_month' => 'required|integer',
            'salary_currency' => 'required|integer',
            'current_salary' => 'required|integer',
            'skills' => 'required|array',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $employment_id = UserEmployment::insertGetId([
                'user_id'=> auth()->user()->id,
                'total_experience_years'=> $request->total_experience_years,
                'total_experience_months'=> $request->total_experience_months,
                'last_designation'=> $request->designation,
                'employer_id'=> $request->employer,
                // 'country_id'=> $request->employer_country,
                'city_id'=> $request->location,
                'currency_id'=> $request->salary_currency,
                'current_salary'=> $request->current_salary,
                'working_since_from_year'=> $request->working_since_from_year,
                'working_since_from_month'=> $request->working_since_from_month,
                'working_since_to_year'=> $request->working_since_to_year,
                'working_since_to_month'=> $request->working_since_to_month,
                'is_current_job'=> 1,
            ]);

            if(!empty($request->skills)){
                foreach($request->skills as $skill){
                    UserEmploymentSkill::insert([
                        'user_id'=> auth()->user()->id,
                        'user_employment_id'=> $employment_id,
                        'keyskill_id'=> $skill,
                    ]);
                }
            }

            return $this->sendResponse($this->getUserDetails(), 'Professional details updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
