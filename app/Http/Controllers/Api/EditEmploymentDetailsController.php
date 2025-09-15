<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Designation;
use App\Models\Employer;
use App\Models\UserEmployment;
use App\Models\UserEmploymentSkill;
use App\Models\Keyskill;

class EditEmploymentDetailsController extends BaseApiController
{
    /**
     * Get educational List.
    */
    public function getEmploymentList()
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
                                ->with('functionalareas')
                                ->with('parkbenefits')
                                ->latest()
                                ->get()
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Get Employment details.
    */
    public function getEmploymentDetails($id)
    {
        try {
            return $this->sendResponse(
                UserEmployment::where('id', $id)
                                ->where('user_id', auth()->user()->id)
                                ->with('employer')
                                ->with('country')
                                ->with('city')
                                ->with('skills')
                                ->with('notice_period')
                                ->with('industrys')
                                ->with('functionalareas')
                                ->with('parkbenefits')
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
            'employer' => 'required',
            'is_current_job'=> 'required|boolean',
            'employment_type' => 'required|string',
            'location' => 'required|integer',
            'skills' => 'required|array',
            'working_since_from_year' => 'required',
            'working_since_from_month' => 'required',
            'disclosing_last_salary' => 'required_if:currently_employed,1|boolean',
            // 'salary_currency' => 'required|integer',
            // 'current_salary' => 'required|integer',
            'notice_period'=> 'required|integer',
        ]);
        if($request->is_current_job == 0){
            $validator = Validator::make($request->all(), [
                'working_since_to_year' => 'required|integer',
                'working_since_to_month' => 'required|integer'
            ]);
        }

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            if($request->is_current_job == 1){
                UserEmployment::where('user_id', auth()->user()->id)->update([
                    'is_current_job'=> 0
                ]);
            }
            $employer = new Employer();
            $employer_id = is_numeric($request->employer) ? $request->employer : $employer->getEmployerId($request->employer);

            $designation = new Designation();
            $designation_id = is_numeric($request->designation) ? $request->designation : $designation->getDesignationId($request->designation);

            UserEmployment::where('id', $id)->update([
                'last_designation'=> $designation_id,
                'employer_id'=> $employer_id,
                'is_current_job'=> $request->is_current_job,
                'employment_type'=> $request->employment_type,
                'city_id'=> $request->location,
                'working_since_from_year'=> $request->working_since_from_year,
                'working_since_from_month'=> $request->working_since_from_month,
                'working_since_to_year'=> $request->working_since_to_year,
                'working_since_to_month'=> $request->working_since_to_month,
                'currency_id'=> $request->salary_currency,
                'current_salary'=> $request->current_salary,
                'disclosing_last_salary'=> $request->disclosing_last_salary,
                'notice_period'=> $request->notice_period,
            ]);
            $this->calculate_profile_completed_percentage(auth()->user()->id, 'employment-details'); //Employment details completes
            if(!empty($request->skills)){
                $keyskillObj = new Keyskill();
                UserEmploymentSkill::where('user_employment_id', $id)->delete();
                foreach($request->skills as $skill){
                    $keyskill_id = is_numeric($skill) ? $skill : $keyskillObj->getDesignationId($skill);
                    UserEmploymentSkill::create([
                        'user_id'=> auth()->user()->id,
                        'user_employment_id'=> $id,
                        'keyskill_id'=> $keyskill_id,
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
            'employer' => 'required',
            'is_current_job'=> 'required|boolean',
            'employment_type' => 'required|string',
            'location' => 'required|integer',
            'skills' => 'required|array',
            'working_since_from_year' => 'required',
            'working_since_from_month' => 'required',
            // 'salary_currency' => 'required|integer',
            // 'current_salary' => 'required|integer',
            'notice_period'=> 'required|integer',
        ]);
        if($request->is_current_job == 0){
            $validator = Validator::make($request->all(), [
                'working_since_to_year' => 'required|integer',
                'working_since_to_month' => 'required|integer'
            ]);
        }

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            if($request->is_current_job == 1){
                UserEmployment::where('user_id', auth()->user()->id)->update([
                    'is_current_job'=> 0
                ]);
            }
            $employer = new Employer();
            $employer_id = is_numeric($request->employer) ? $request->employer : $employer->getEmployerId($request->employer);

            $designation = new Designation();
            $designation_id = is_numeric($request->designation) ? $request->designation : $designation->getDesignationId($request->designation);

            $employment_id = UserEmployment::insertGetId([
                'user_id'=> auth()->user()->id,
                'last_designation'=> $designation_id,
                'employer_id'=> $employer_id,
                'is_current_job'=> $request->is_current_job,
                'employment_type'=> $request->employment_type,
                'city_id'=> $request->location,
                'working_since_from_year'=> $request->working_since_from_year,
                'working_since_from_month'=> $request->working_since_from_month,
                'working_since_to_year'=> $request->is_current_job == 0 ? $request->working_since_to_year : 0,
                'working_since_to_month'=> $request->is_current_job == 0 ? $request->working_since_to_month : 0,
                'currency_id'=> $request->salary_currency,
                'current_salary'=> $request->current_salary,
                'disclosing_last_salary'=> $request->disclosing_last_salary,
                'notice_period'=> $request->notice_period,
                'created_at'=> date('Y-m-d h:i:s')
            ]);
            if($employment_id){
                $this->calculate_profile_completed_percentage(auth()->user()->id, 'employment-details'); //Employment details completes
                if(!empty($request->skills)){
                    $keyskillObj = new Keyskill();
                    foreach($request->skills as $skill){
                        $keyskill_id = is_numeric($skill) ? $skill : $keyskillObj->getDesignationId($skill);
                        UserEmploymentSkill::create([
                            'user_id'=> auth()->user()->id,
                            'user_employment_id'=> $employment_id,
                            'keyskill_id'=> $keyskill_id,
                        ]);
                    }

                    $this->calculate_profile_completed_percentage(auth()->user()->id, 'key-skills'); //Key skills completes
                }

                return $this->sendResponse($this->getUserDetails(), 'Employment details added successfully.');
            }else{
                return $this->sendResponse($this->getUserDetails(), 'Sorry!! Unable to add employment details.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
