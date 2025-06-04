<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserResume;
use App\Models\UserItSkill;
use App\Models\UserEmployment;
use App\Models\UserSkill;
use App\Models\ProfileComplete;
use App\Models\UserProfileCompletedPercentage;
use App\Models\UserEmploymentSkill;
use App\Models\UserEmploymentIndustry;
use App\Models\UserEmploymentFunctionalArea;
use App\Models\UserEmploymentParkBenefit;

class EditProfessionalDetailsController extends BaseApiController
{
    /**
     * Get resume headline.
    */
    public function getResumeHeadline()
    {
        try {
            return $this->sendResponse(
                UserProfile::select('resume_headline')
                            ->where('user_id', auth()->user()->id)
                            ->first()
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Post resume headline.
    */
    public function postResumeHeadline(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'resume_headline' => 'required|string|max:255'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            UserProfile::where('user_id', auth()->user()->id)->update([
                'resume_headline'=> $request->resume_headline,
            ]);

            return $this->sendResponse($this->getUserDetails(), 'Resume Headline updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Get resume headline.
    */
    public function getKeyskills()
    {
        try {
            return $this->sendResponse(
                User::where('id', auth()->user()->id)->with('user_skills')->first()->user_skills
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Post resume headline.
    */
    public function postKeyskills(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyskills' => 'required|array'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            if(!empty($request->keyskills)){
                UserSkill::where('user_id', auth()->user()->id)->delete();
                foreach($request->keyskills as $keyskill){
                    UserSkill::insert([
                        'user_id'=> auth()->user()->id,
                        'keyskill_id'=> $keyskill,
                        'proficiency_level' => 'Beginner',
                        'is_primary'=> 1
                    ]);
                }

                $this->calculate_profile_completed_percentage(auth()->user()->id, 'key-skills'); //Key skills completes
            }

            return $this->sendResponse($this->getUserDetails(), 'Key skills updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Get itskills.
    */
    public function getItskills()
    {
        try {
            return $this->sendResponse(
                UserItSkill::where('user_id', auth()->user()->id)
                    ->with('key_skills')
                    ->get()
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Post resume itskills.
    */
    public function postItskills(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'itkill_id' => 'required|integer',
            'version' => 'required|integer',
            'exp_year' => 'required|integer',
            'exp_month' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            if(!empty($request->keyskills)){
                UserItSkill::insert([
                    'user_id'=> auth()->user()->id,
                    'itkill_id'=> $request->itkill_id,
                    'last_used'=> $request->last_used,
                    'exp_year'=> $request->exp_year,
                    'exp_month'=> $request->exp_month,
                ]);

                $this->calculate_profile_completed_percentage(auth()->user()->id, 'key-skills'); //Key skills completes
            }

            return $this->sendResponse($this->getUserDetails(), 'Key skills updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Get professional details.
    */
    public function getProfessionalDetails()
    {
        try {
            return $this->sendResponse(
                UserEmployment::where('user_id', auth()->user()->id)
                                ->where('is_current_job', 1)
                                ->with('employer')
                                ->with('countrie')
                                ->with('city')
                                ->with('skills')
                                ->first()
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Post professional details.
    */
    public function postProfessionalDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'professional_id' => 'nullable|integer',
            'total_experience_years' => 'required|integer',
            'total_experience_months' => 'required|integer',
            'industry' => 'required|array',
            'functional_area' => 'required|array',
            'work_level' => 'required|integer',
            'salary_currency' => 'required|integer',
            'current_salary' => 'required|integer',
            'perk_benefits' => 'required|array'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            if(!empty($request->professional_id)){
                $id = $request->professional_id;
                UserEmployment::where('id', $id)->update([
                    'total_experience_years'=> $request->total_experience_years,
                    'total_experience_months'=> $request->total_experience_months,
                    'work_level'=> $request->work_level,
                    'currency_id'=> $request->salary_currency,
                    'current_salary'=> $request->current_salary,
                    'is_current_job'=> 1,
                ]);
            }else{
                $id = UserEmployment::insertGetId([
                    'user_id'=> auth()->user()->id,
                    'total_experience_years'=> $request->total_experience_years,
                    'total_experience_months'=> $request->total_experience_months,
                    'work_level'=> $request->work_level,
                    'currency_id'=> $request->salary_currency,
                    'current_salary'=> $request->current_salary,
                    'is_current_job'=> 1,
                ]);
            }

            if(!empty($request->industry)){
                UserEmploymentIndustry::where('user_employment_id', $id)->delete();
                foreach($request->industry as $industry_id){
                    UserEmploymentIndustry::insert([
                        'user_id'=> auth()->user()->id,
                        'user_employment_id'=> $id,
                        'industry'=> $industry_id,
                    ]);
                }
            }

            if(!empty($request->functional_area)){
                UserEmploymentFunctionalArea::where('user_employment_id', $id)->delete();
                foreach($request->functional_area as $functional_area_id){
                    UserEmploymentFunctionalArea::insert([
                        'user_id'=> auth()->user()->id,
                        'user_employment_id'=> $id,
                        'functional_area'=> $functional_area_id,
                    ]);
                }
            }

            if(!empty($request->perk_benefits)){
                UserEmploymentParkBenefit::where('user_employment_id', $id)->delete();
                foreach($request->perk_benefits as $perk_benefit_id){
                    UserEmploymentParkBenefit::insert([
                        'user_id'=> auth()->user()->id,
                        'user_employment_id'=> $id,
                        'perk_benefit'=> $perk_benefit_id,
                    ]);
                }
            }

            return $this->sendResponse($this->getUserDetails(), 'Professional details updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Get profile summery.
    */
    public function getProfileSummery()
    {
        try {
            return $this->sendResponse(
                UserProfile::select('profile_summery')
                    ->where('user_id', auth()->user()->id)
                    ->first()
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Post profile summery.
    */
    public function postProfileSummery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_summery' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            UserProfile::where('user_id', auth()->user()->id)->update([
                'profile_summery'=> $request->profile_summery,
            ]);

            $this->calculate_profile_completed_percentage(auth()->user()->id, 'profile-summary'); //Profile Summary completes
            return $this->sendResponse($this->getUserDetails(), 'Profile Summery updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Get profile completed percentages.
    */
    public function getProfileCompletedPercentages()
    {
        try {
            $list = ProfileComplete::where('status', 1)
                                ->get()->toArray();
            if(!empty($list)){
                foreach($list as $index => $arr){
                    $has_data = UserProfileCompletedPercentage::where('user_id', auth()->user()->id)
                                                                ->where('slug', $arr['slug'])
                                                                ->count();

                    $list[$index]['is_completed'] = $has_data > 0 ? 1 : 0;
                }
            }

            return $this->sendResponse(
                $list
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
