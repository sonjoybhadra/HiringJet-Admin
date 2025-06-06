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
                                ->with('country')
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
            'designation' => 'required|string',
            'employer' => 'required|string',
            'is_current_job'=> 'required|boolean',
            'employment_type' => 'required|integer',
            'location' => 'required|integer',
            'skills' => 'required|array',
            'working_since_from_year' => 'required|integer',
            'working_since_from_month' => 'required|integer',
            'working_since_to_year' => 'required|integer',
            'working_since_to_month' => 'required|integer',
            'salary_currency' => 'required|integer',
            'current_salary' => 'required|integer',
            'notice_period'=> 'required|integer',
            /* 'total_experience_years' => 'required|integer',
            'total_experience_months' => 'required|integer',
            'industry' => 'required|integer',
            'functional_area' => 'required|integer', */
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
                'last_designation'=> $request->designation,
                'employer_id'=> $request->employer,
                'is_current_job'=> $request->is_current_job,
                'employment_type'=> $request->employment_type,
                'city_id'=> $request->location,
                'working_since_from_year'=> $request->working_since_from_year,
                'working_since_from_month'=> $request->working_since_from_month,
                'working_since_to_year'=> $request->working_since_to_year,
                'working_since_to_month'=> $request->working_since_to_month,
                'currency_id'=> $request->salary_currency,
                'current_salary'=> $request->current_salary,
                'notice_period'=> $request->notice_period,
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

            return $this->sendResponse($this->getUserDetails(), 'Employment details updated successfully.');
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
            'designation' => 'required|string',
            'employer' => 'required|string',
            'is_current_job'=> 'required|boolean',
            'employment_type' => 'required|integer',
            'location' => 'required|integer',
            'skills' => 'required|array',
            'working_since_from_year' => 'required|integer',
            'working_since_from_month' => 'required|integer',
            'working_since_to_year' => 'required|integer',
            'working_since_to_month' => 'required|integer',
            'salary_currency' => 'required|integer',
            'current_salary' => 'required|integer',
            'notice_period'=> 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $employment_id = UserEmployment::insertGetId([
                'user_id'=> auth()->user()->id,
                'last_designation'=> $request->designation,
                'employer_id'=> $request->employer,
                'is_current_job'=> $request->is_current_job,
                'employment_type'=> $request->employment_type,
                'city_id'=> $request->location,
                'working_since_from_year'=> $request->working_since_from_year,
                'working_since_from_month'=> $request->working_since_from_month,
                'working_since_to_year'=> $request->working_since_to_year,
                'working_since_to_month'=> $request->working_since_to_month,
                'currency_id'=> $request->salary_currency,
                'current_salary'=> $request->current_salary,
                'notice_period'=> $request->notice_period
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

            return $this->sendResponse($this->getUserDetails(), 'Employment details updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
