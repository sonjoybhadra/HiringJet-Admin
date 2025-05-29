<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserEducation;
use App\Models\ProfileComplete;
use App\Models\UserProfileCompletedPercentage;

class EditEducationalDetailsController extends BaseApiController
{
    /**
     * Get educational details.
    */
    public function getEducationalDetails()
    {
        try {
            return $this->sendResponse(
                UserEducation::where('user_id', auth()->user()->id)
                                ->with('qualification')
                                ->with('course')
                                ->with('location')
                                ->with('university')
                                ->with('specialization')
                                ->first()
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Post educational details.
    */
    public function postEducationalDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qualification' => 'required|integer',
            'course' => 'required|integer',
            'specialization' => 'required|integer',
            'location' => 'required|integer',
            'university' => 'required|integer',
            'passing_year' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            UserEducation::where('user_id', auth()->user()->id)->update([
                'resume_headline'=> $request->resume_headline,
            ]);

            /* if(!empty($request->language)){
                UserEducation::where('user_id', auth()->user()->id)->delete();
                foreach($request->language as $index => $language){
                    UserEducation::insert([
                        'user_id'=> auth()->user()->id,
                        'qualification_id'=> $request->qualification,
                        'course_id'=> $request->course,
                        'specialization_id'=> $request->specialization,
                        'location_id' => $request->location,
                        'university_id'=> $request->university,
                        'passing_year'=> $request->passing_year
                    ]);
                }
            } */

            return $this->sendResponse([], 'Education details updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
}
