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
use App\Models\UserEducation;
use App\Models\UserEmployment;
use App\Models\UserSkill;
use App\Models\ProfileComplete;
use App\Models\UserProfileCompletedPercentage;

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
                UserSkill::where('user_id', auth()->user()->id)
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
            'itskills' => 'required|array'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            if(!empty($request->keyskills)){
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
     * Get professional details.
    */
    public function getProfessionalDetails()
    {
        try {
            return $this->sendResponse(
                UserEmployment::where('user_id', auth()->user()->id)
                                ->with('employer')
                                ->with('countrie')
                                ->with('citie')
                                ->with('BelongsTo')
                                ->latest()
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
            'keyskills' => 'required|array'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            if(!empty($request->keyskills)){
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
